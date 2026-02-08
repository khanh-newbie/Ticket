<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSchedule extends Model
{
    use HasFactory;

    //     1 = upcoming

    // 2 = ongoing

    // 3 = completed

    // 4 = cancelled
    const STATUS_UPCOMING = 1;
    const STATUS_ONGOING = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_CANCELLED = 4;
    protected $table = 'event_schedules';
    protected $fillable = [
        'event_id',
        'start_datetime',
        'end_datetime',
        'status',
        'available_tickets',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class, 'schedule_id');
    }
}
