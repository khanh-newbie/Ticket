<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    //draft / pending_review / published / cancelled / completed
    const STATUS_DRAFT = 1;
    const STATUS_PENDING_REVIEW = 2;
    const STATUS_PUBLISHED = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_COMPLETED = 5;

    protected $table = 'events';
    protected $fillable = [
        'organizer_id',
        'venue_id',
        'category_id',
        'event_name',
        'description',
        'background_image_url',
        'poster_image_url',
        'status',
    ];
    protected $appends = [
        'ticket_types_min_base_price',
    ];

    public function getTicketTypesMinBasePriceAttribute()
    {
        return $this->ticketTypes()->min('base_price');
    }


    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(EventCategory::class, 'event_category_map', 'event_id', 'category_id');
    }

    public function schedules()
    {
        return $this->hasMany(EventSchedule::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }
    
    // Tính điểm trung bình sao
    public function getAvgRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }
    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function ticketTypes()
    {
        // Qua bảng event_schedules
        return $this->hasManyThrough(
            TicketType::class,
            EventSchedule::class,
            'event_id',    // FK trên event_schedules
            'schedule_id', // FK trên ticket_types
            'id',          // local key trên events
            'id'           // local key trên event_schedules
        );
    }
}
