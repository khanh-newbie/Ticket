<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'event_id', 
        'review_id', 
        'reason', 
        'description', 
        'status'
    ];

    // Người gửi báo cáo
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Nếu báo cáo Event
    public function event() {
        return $this->belongsTo(Event::class);
    }

    // Nếu báo cáo Review
    public function review() {
        return $this->belongsTo(Review::class);
    }
}
