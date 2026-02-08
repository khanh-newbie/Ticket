<?php

namespace App\Jobs;

use App\Mail\PaymentSuccessMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use App\Models\Ticket; // <--- Nhớ import Model Ticket
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendTicketEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        try {
            // Eager load
            $this->order->load('user');
            
            $ticketsForEmail = [];
            $orderItems = OrderItem::where('order_id', $this->order->id)->get();

            foreach ($orderItems as $item) {
                $ticketType = TicketType::find($item->ticket_type_id);
                $ticketName = $ticketType->name ?? 'Vé sự kiện';

                // Lặp qua số lượng để tạo từng vé lẻ
                for ($i = 0; $i < $item->quantity; $i++) {
                    
                    // 1. Tạo mã vé duy nhất
                    // Cấu trúc: ORD-{OrderId}-{TicketTypeId}-{RandomString}
                    $uniqueTicketCode = 'ORD-' . $this->order->id . '-' . $item->ticket_type_id . '-' . Str::upper(Str::random(6));
                    
                    // 2. LƯU VÉ VÀO DATABASE (QUAN TRỌNG)
                    // Lưu cái mã này lại để sau này đối chiếu check-in
                    Ticket::create([
                        'order_item_id'  => $item->id,
                        'ticket_type_id' => $item->ticket_type_id,
                        'qr_code'        => $uniqueTicketCode, // Lưu chuỗi mã vé
                        'status'         => 'valid',           // Mặc định là có hiệu lực
                        'issued_at'      => now(),
                    ]);

                    // 3. Tạo Link QR Code online cho Email
                    $qrUrl = "https://quickchart.io/qr?text=" . urlencode($uniqueTicketCode) . "&size=300&margin=1&ecLevel=H";

                    // Đưa vào mảng để gửi mail
                    $ticketsForEmail[] = [
                        'name'    => $ticketName,
                        'price'   => $item->unit_price,
                        'code'    => $uniqueTicketCode,
                        'qr_url'  => $qrUrl
                    ];
                }
            }

            // Gửi mail
            $userEmail = $this->order->user->email ?? null;

            if ($userEmail) {
                Mail::to($userEmail)->send(new PaymentSuccessMail($this->order, $ticketsForEmail));
                Log::info("Đã tạo vé DB và gửi mail cho Order #" . $this->order->id);
            }

        } catch (\Exception $e) {
            Log::error("Job Failed (Order {$this->order->id}): " . $e->getMessage());
            // throw $e; 
        }
    }
}