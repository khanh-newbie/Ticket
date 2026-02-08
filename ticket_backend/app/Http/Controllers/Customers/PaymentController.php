<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\PayOSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendTicketEmailJob;
class PaymentController extends Controller
{
    // =========================================================================
    // PRIVATE HELPER FUNCTIONS
    // =========================================================================

    /**
     * Helper: Hoàn lại số lượng tồn kho khi hủy/lỗi
     */
    private function restoreStock(Order $order)
    {
        $orderItems = OrderItem::where('order_id', $order->id)->get();

        foreach ($orderItems as $item) {
            // Cộng lại số lượng vào kho
            TicketType::where('id', $item->ticket_type_id)
                ->increment('available_quantity', $item->quantity);
        }
    }

    /**
     * Helper: Xóa các item đã mua khỏi giỏ hàng (Cách 2)
     */
    private function clearCart($userId, Order $order)
    {
        // 1. Tìm giỏ hàng của user
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) return;

        // 2. Lấy danh sách ID các loại vé CÓ trong Order vừa thanh toán
        $purchasedTicketTypeIds = OrderItem::where('order_id', $order->id)
            ->pluck('ticket_type_id')
            ->toArray();

        if (empty($purchasedTicketTypeIds)) return;

        // 3. Chỉ xóa các item trùng khớp trong bảng cart_items
        CartItem::where('cart_id', $cart->id)
            ->whereIn('ticket_type_id', $purchasedTicketTypeIds)
            ->delete();
            
        // (Tùy chọn) Xóa cart rỗng nếu cần
        // if (CartItem::where('cart_id', $cart->id)->doesntExist()) $cart->delete();
    }

    // =========================================================================
    // PUBLIC FUNCTIONS (API)
    // =========================================================================

    /**
     * Tạo order và link thanh toán online
     */
    public function createOrder(Request $request)
    {
        $userId = Auth::id() ?? $request->input('user_id');
        $params = $request->all();

        if (empty($params['items']) || !is_array($params['items'])) {
            return response()->json(['code' => 400, 'message' => 'Giỏ hàng trống'], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Tạo Order (Pending)
            $order = Order::create([
                'user_id' => $userId,
                'order_code' => '', // Tạo xong sẽ update lại
                'total_amount' => 0,
                'discount_amount' => 0,
                'final_amount' => 0,
                'payment_status' => 'pending',
                'return_path' => $params['return_path'] ?? '',
                'payment_method' => $params['payment_method'] ?? 'payos',
            ]);

            $order->update(['order_code' => $order->id]);

            $orderItemsData = [];
            $total = 0;

            // 2. Xử lý từng item
            foreach ($params['items'] as $item) {
                if (!isset($item['ticket_type_id'], $item['quantity'])) continue;

                // --- QUAN TRỌNG: Lock row để tránh race condition ---
                $ticketType = TicketType::where('id', $item['ticket_type_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$ticketType) {
                    DB::rollBack();
                    return response()->json(['code' => 404, 'message' => 'Không tìm thấy vé'], 404);
                }

                // Kiểm tra số lượng tồn
                if ($ticketType->available_quantity < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'code' => 400, 
                        'message' => "Vé '{$ticketType->name}' chỉ còn {$ticketType->available_quantity}, không đủ."
                    ], 400);
                }

                // Trừ kho
                $ticketType->decrement('available_quantity', $item['quantity']);

                // Tính tiền
                $subtotal = $ticketType->base_price * $item['quantity'];
                $total += $subtotal;

                // Lưu OrderItem
                OrderItem::create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $ticketType->base_price,
                    'subtotal' => $subtotal,
                ]);

                // Chuẩn bị data cho PayOS
                $orderItemsData[] = [
                    'name' => $ticketType->name,
                    'quantity' => $item['quantity'],
                    'price' => (float)$ticketType->base_price,
                ];
            }

            $order->update(['total_amount' => $total, 'final_amount' => $total]);

            // 3. Tạo link PayOS
            Log::info('payload_payos', ['order' => $order, 'items' => $orderItemsData]);
            
            $paymentLink = PayOSService::createPaymentLink($order, $orderItemsData);

            if (!$paymentLink || !isset($paymentLink['checkoutUrl'])) {
                DB::rollBack(); // PayOS lỗi -> Rollback kho
                return response()->json(['code' => 500, 'message' => 'Lỗi tạo link thanh toán'], 500);
            }

            DB::commit(); // Thành công -> Lưu DB

            return response()->json([
                'code' => 200,
                'message' => 'Order tạo thành công',
                'data' => [
                    'order_id' => $order->id,
                    'checkout_url' => $paymentLink['checkoutUrl']
                ]
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json(['code' => 500, 'message' => 'Lỗi server', 'error' => $th->getMessage()], 500);
        }
    }

    /**
     * Kiểm tra trạng thái thanh toán (API Check)
     */
    public function checkPaymentStatus(Request $request)
    {
        $orderCode = $request->order_code;
        if (!$orderCode) return response()->json(['code' => 400, 'message' => 'Thiếu order_code'], 400);

        try {
            $data = PayOSService::getPaymentStatus($orderCode);
            if (!$data) return response()->json(['code' => 500, 'message' => 'Lỗi lấy trạng thái'], 500);

            $order = Order::where('order_code', $orderCode)->first();
            if ($order) {
                $status = $data['status'] ?? 'FAILED';
                $order->payment_status = match ($status) {
                    'PAID' => 'paid',
                    'PENDING' => 'pending',
                    default => 'failed'
                };
                $order->save();
            }

            return response()->json(['code' => 200, 'message' => 'Success', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Xử lý khi khách hàng thanh toán xong và được chuyển về (Return URL)
     */
    public function payosReturn(Request $request)
    {
        $payosOrderCode = $request->orderCode;
        $frontendUrl = 'http://localhost:5173'; // Tốt nhất nên dùng config('app.frontend_url')

        if (!$payosOrderCode) return redirect("$frontendUrl/cart?status=error&message=missing_code");

        // Eager load user để Job dùng luôn, đỡ phải query lại trong Job
        $order = Order::where('order_code', $payosOrderCode)->with('user')->first();
        
        if (!$order) return redirect("$frontendUrl/cart?status=error&message=order_not_found");

        // Nếu đã PAID rồi -> chuyển về cart báo success luôn
        if ($order->payment_status === 'paid') {
            return redirect("$frontendUrl/cart?status=success&id={$order->id}");
        }

        try {
            $paymentInfo = PayOSService::getPaymentStatus($payosOrderCode);
            
            // 1. THANH TOÁN THẤT BẠI
            if (!$paymentInfo || ($paymentInfo['status'] ?? '') !== 'PAID') {
                if ($order->payment_status === 'pending') {
                    $order->update(['payment_status' => 'failed']);
                    $this->restoreStock($order);
                }
                return redirect("$frontendUrl/cart?status=failed&id={$order->id}");
            }

            // 2. THANH TOÁN THÀNH CÔNG
            if ($order->payment_status === 'pending') {
                $order->update(['payment_status' => 'paid']);
                
                // Xóa item đã mua khỏi Cart
                $this->clearCart($order->user_id, $order);

                // --- GỌI QUEUE JOB ---
                // Việc nặng (tạo QR, gửi mail) sẽ được xử lý ngầm
                SendTicketEmailJob::dispatch($order);
            }
            
            // Redirect NGAY LẬP TỨC để user không phải chờ
            return redirect("$frontendUrl/cart?status=success&id={$order->id}");

        } catch (\Exception $e) {
            Log::error("PayOS Return Error: " . $e->getMessage());
            return redirect("$frontendUrl/cart?status=error&message=system_error&id={$order->id}");
        }
    }

    /**
     * Xử lý khi khách hàng hủy thanh toán (Cancel URL)
     */
    public function payosCancel(Request $request)
    {
        $payosOrderCode = $request->orderCode;
        $frontendUrl = 'http://localhost:5173';

        if (!$payosOrderCode) return redirect("$frontendUrl/cart");

        $order = Order::where('order_code', $payosOrderCode)->first();
        if (!$order) return redirect("$frontendUrl/cart");

        // Nếu đã thanh toán rồi thì vẫn báo success
        if ($order->payment_status === 'paid') {
            return redirect("$frontendUrl/cart?status=success&id={$order->id}");
        }

        // Xử lý Hủy
        if ($order->payment_status === 'pending') {
            $order->update(['payment_status' => 'cancelled']);
            
            // QUAN TRỌNG: Hoàn lại kho ngay lập tức
            $this->restoreStock($order);
        }

        // Redirect về Cart kèm status cancelled
        return redirect("$frontendUrl/cart?status=cancelled&id={$order->id}");
    }
}