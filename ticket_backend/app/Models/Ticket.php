<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $table = 'tickets';
    protected $fillable = [
        'order_item_id',
        'ticket_type_id',
        'qr_code',
        'seat_number',
        'status',
        'issued_at',
    ];
    // App\Models\Ticket.php

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }
}
