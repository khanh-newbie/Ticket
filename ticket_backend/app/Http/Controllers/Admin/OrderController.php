<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseApi;
use App\Models\Order;

class OrderController extends Controller
{
    protected $response;

    public function __construct()
    {
        $this->response = new ResponseApi();
    }

    public function paidOrders()
    {
        $orders = Order::query()
            ->with([
                'user:id,name',
                'items.ticketType.schedule.event:id,event_name',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($order) {

                // Tổng số vé
                $totalTickets = $order->items->sum('quantity');

                // Lấy tên sự kiện (lấy từ item đầu)
                $eventName = optional(
                    optional(
                        optional(
                            $order->items->first()
                        )->ticketType
                    )->schedule
                )->event->event_name ?? 'N/A';

                return [
                    'id' => $order->id,
                    'buyer_name' => $order->user->name ?? 'N/A',
                    'event_name' => $eventName,
                    'ticket_quantity' => $totalTickets,
                    'final_amount' => $order->final_amount,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'created_at' => $order->created_at,
                ];
            });

        return $this->response->success($orders);
    }
}