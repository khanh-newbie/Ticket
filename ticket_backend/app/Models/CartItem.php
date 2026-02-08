<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;
    protected $table = 'cart_items';
    protected $fillable = [
        'cart_id',
        'ticket_type_id',
        'quantity',
        'added_at',
    ];

    // App\Models\CartItem.php

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }
}
