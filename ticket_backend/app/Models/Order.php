<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $fillable = [
        'user_id',
        'order_code',
        'coupon_id',
        'total_amount',
        'discount_amount',
        'final_amount',
        'payment_status',
        'payment_method',
        'return_path'
    ];
    // App\Models\Order.php

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function review()
    {
        // Một đơn hàng chỉ có 1 review
        return $this->hasOne(Review::class);
    }

    public function tickets()
    {
        // Đi vòng qua order_items
        return $this->hasManyThrough(
            Ticket::class,
            OrderItem::class,
            'order_id',   // FK trên order_items
            'order_item_id', // FK trên tickets
            'id',
            'id'
        );
    }
}
