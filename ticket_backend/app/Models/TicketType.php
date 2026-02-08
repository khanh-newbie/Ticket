<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;
    //     1 = active

    // 2 = sold_out

    // 3 = inactive
    const STATUS_ACTIVE = 1;
    const STATUS_SOLD_OUT = 2;
    const STATUS_INACTIVE = 3;
    protected $table = 'ticket_types';
    protected $fillable = [
        'schedule_id',
        'name',
        'base_price',
        'total_quantity',
        'available_quantity',
        'status',
    ];

    public function schedule()
    {
        return $this->belongsTo(EventSchedule::class, 'schedule_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
