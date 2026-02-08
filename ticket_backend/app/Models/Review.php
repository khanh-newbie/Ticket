<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'event_id', 
        'user_id', 
        'order_id', // Thêm cái này
        'rating', 
        'comment',
        'status'
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function order() {
        return $this->belongsTo(Order::class);
    }
}