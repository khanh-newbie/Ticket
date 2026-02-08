<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use PayOS\PayOS;

class PayOSService
{
    private $payOS;

    public function __construct()
    {
        $this->payOS = new PayOS(
            env('PAYOS_CLIENT_ID'),
            env('PAYOS_API_KEY'),
            env('PAYOS_CHECKSUM_KEY')
        );
    }

    /**
     * Tạo link thanh toán online
     */
    public static function createPaymentLink(Order $order, $items)
    {
        $self = new self;
        $returnUrlWithId = url('api/payment/payos/return') . '?ref_id=' . $order->id;
        $cancelUrlWithId = url('api/payment/payos/cancel') . '?ref_id=' . $order->id;
        try {
            $data = [
                'orderCode' => $order->order_code,
                'amount' => $order->final_amount,
                'description' => "Thanh toán đơn hàng #{$order->id}",
                'items' => $items,
                'returnUrl' => $returnUrlWithId, // Truyền link đã kèm ID
                'cancelUrl' => $cancelUrlWithId, // Truyền link đã kèm ID
                'expiredAt' => Carbon::now()->addMinutes(10)->timestamp
            ];

            $result = $self->payOS->createPaymentLink($data);

            Log::info('payos_response', $result);

            return $result;
        } catch (\Throwable $th) {
            Log::error('payos_exception', ['msg' => $th->getMessage()]);
            return null;
        }
    }

    /**
     * Lấy trạng thái thanh toán
     */
    public static function getPaymentStatus($orderId)
    {
        $self = new self;

        try {
            $response = $self->payOS->getPaymentLinkInformation($orderId);
            Log::info("payos_status", $response);
            return $response;
        } catch (\Throwable $th) {
            Log::error("payos_status_error", [$th->getMessage()]);
            return null;
        }
    }
}
