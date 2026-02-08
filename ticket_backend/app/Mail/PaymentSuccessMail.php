<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $tickets; // Mảng chứa thông tin từng vé lẻ (bao gồm mã QR)

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, array $tickets)
    {
        $this->order = $order;
        $this->tickets = $tickets;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Xác nhận thanh toán đơn hàng #' . $this->order->id)
                    ->view('mails.payment_success');
    }
}