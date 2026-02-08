<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // 1. QUERY BUILDER: Lấy dữ liệu phẳng
        $rawItems = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('ticket_types', 'order_items.ticket_type_id', '=', 'ticket_types.id')
            ->join('event_schedules', 'ticket_types.schedule_id', '=', 'event_schedules.id')
            ->join('events', 'event_schedules.event_id', '=', 'events.id')
            ->leftJoin('venues', 'events.venue_id', '=', 'venues.id')
            // JOIN REVIEW: Join theo User + Event + Order (Để biết user đã review event này trong đơn này chưa)
            ->leftJoin('reviews', function($join) use ($userId) {
                $join->on('events.id', '=', 'reviews.event_id')
                     ->on('orders.id', '=', 'reviews.order_id') // Ràng buộc review thuộc đơn hàng này
                     ->where('reviews.user_id', '=', $userId);
            })
            ->select(
                // --- Level 1: Order ---
                'orders.id as order_id',
                'orders.order_code',
                'orders.final_amount',
                'orders.payment_status',
                'orders.created_at as order_created_at',
                
                // --- Level 2: Event ---
                'events.id as event_id',
                'events.event_name',
                'events.poster_image_url',
                'events.background_image_url',
                'venues.name as venue_name',
                //'venues.address as venue_address',

                // --- Level 3: Schedule ---
                'event_schedules.id as schedule_id',
                'event_schedules.start_datetime',
                'event_schedules.end_datetime',
                
                // --- Level 4: Ticket Item ---
                'ticket_types.name as ticket_name',
                'order_items.quantity',
                'order_items.unit_price',
                
                // --- Review Info (Gắn với Event) ---
                'reviews.rating',
                'reviews.comment'
            )
            ->where('orders.user_id', $userId)
            ->orderBy('orders.created_at', 'desc')
            ->get();

        // 2. GROUPING DATA (3 Levels)
        $formattedOrders = $rawItems->groupBy('order_id')->map(function ($itemsInOrder) {
            
            $orderInfo = $itemsInOrder->first();

            // GROUP BY EVENT
            $events = $itemsInOrder->groupBy('event_id')->map(function ($itemsInEvent) {
                $eventInfo = $itemsInEvent->first();

                // Xử lý ảnh & địa điểm
                $image = $eventInfo->poster_image_url 
                    ?? $eventInfo->background_image_url 
                    ?? '/images/default.jpg';
                
                $location = $eventInfo->venue_name 
                    ? $eventInfo->venue_name . ', ' . ($eventInfo->venue_address ?? '') 
                    : 'Chưa cập nhật';

                // GROUP BY SCHEDULE (Tách suất diễn)
                $schedules = $itemsInEvent->groupBy('schedule_id')->map(function ($itemsInSchedule) {
                    $scheduleInfo = $itemsInSchedule->first();

                    // LIST TICKETS
                    $tickets = $itemsInSchedule->map(function ($item) {
                        return [
                            'ticketName' => $item->ticket_name,
                            'quantity'   => $item->quantity,
                            'unitPrice'  => $item->unit_price,
                            'total'      => $item->quantity * $item->unit_price
                        ];
                    });

                    return [
                        'scheduleId' => $scheduleInfo->schedule_id,
                        'startDate'  => $scheduleInfo->start_datetime,
                        'endDate'    => $scheduleInfo->end_datetime,
                        'tickets'    => $tickets->values()
                    ];
                });

                // Review Object (Nếu có)
                $review = $eventInfo->rating ? [
                    'rating'  => $eventInfo->rating,
                    'comment' => $eventInfo->comment,
                ] : null;

                return [
                    'eventId'   => $eventInfo->event_id,
                    'eventName' => $eventInfo->event_name,
                    'eventImage'=> $image,
                    'location'  => $location,
                    'schedules' => $schedules->values(),
                    'review'    => $review // Review nằm ở cấp Event
                ];
            });

            return [
                'id'         => $orderInfo->order_id,
                'code'       => $orderInfo->order_code ?? 'ORD-' . $orderInfo->order_id,
                'totalPrice' => $orderInfo->final_amount,
                'status'     => $orderInfo->payment_status,
                'createdAt'  => $orderInfo->order_created_at,
                'events'     => $events->values()
            ];
        })->values();

        return response()->json([
            'status' => 200,
            'data'   => $formattedOrders
        ]);
    }

    public function getOrderDetails(Request $request, $orderId)
    {
        $userId = Auth::id();

        // 1. Validate Order Owner
        $order = DB::table('orders')
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json(['status' => 404, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        // 2. Get Ticket Details (Kèm mã Code QR)
        // Lưu ý: Cần join bảng `tickets` để lấy từng mã vé cụ thể (code, status, seat...)
        $rawDetails = DB::table('order_items')
            ->join('ticket_types', 'order_items.ticket_type_id', '=', 'ticket_types.id')
            ->join('event_schedules', 'ticket_types.schedule_id', '=', 'event_schedules.id')
            ->join('events', 'event_schedules.event_id', '=', 'events.id')
            ->leftJoin('tickets', 'order_items.id', '=', 'tickets.order_item_id') // Join tickets để lấy mã QR
            ->where('order_items.order_id', $orderId)
            ->select(
                // Event Info
                'events.id as event_id',
                'events.event_name',
                
                // Ticket Type Info
                'ticket_types.name as ticket_type_name',
                
                // Ticket Specific Info (QR Code)
                'tickets.qr_code as ticket_code',
                'tickets.status as ticket_status', // unused, used, etc.
                // 'tickets.seat_number' // Nếu có số ghế
            )
            ->get();

        // 3. Grouping Data (Để FE dễ hiển thị)
        // Group theo Event -> Ticket Type -> List Codes
        $groupedTickets = $rawDetails->groupBy('event_id')->map(function ($itemsInEvent) {
            $firstItem = $itemsInEvent->first();
            
            // Nhóm các mã vé (QR) theo loại vé
            $ticketCodes = $itemsInEvent->map(function ($item) {
                return [
                    'code'   => $item->ticket_code ?? 'PENDING', // Mã vé
                    //'seat'   => $item->seat_number,
                    'isUsed' => $item->ticket_status === 'used' // Trạng thái sử dụng
                ];
            })->values();

            return [
                'eventName'      => $firstItem->event_name,
                'ticketTypeName' => $firstItem->ticket_type_name, // Nếu 1 event có nhiều loại vé, cần group cấp 2 ở đây. Tạm thời lấy loại đầu tiên.
                // Để chuẩn xác hơn nếu đơn hàng có nhiều loại vé cho cùng 1 sự kiện:
                // Bạn nên group theo ticket_type_name trước, nhưng để đơn giản cho UI hiện tại:
                'ticketCodes'    => $ticketCodes
            ];
        })->values();

        // 4. Return Response
        return response()->json([
            'status' => 200,
            'data'   => [
                'id'           => $order->id,
                'code'         => $order->order_code ?? 'ORD-'.$order->id,
                'status'       => $order->payment_status,
                'totalPrice'   => $order->final_amount,
                'createdAt'    => $order->created_at,
                'tickets'      => $groupedTickets // Mảng chứa thông tin vé + QR
            ]
        ]);
    }
}