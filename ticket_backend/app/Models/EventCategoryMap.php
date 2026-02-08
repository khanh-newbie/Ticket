<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCategoryMap extends Model
{
    use HasFactory;

    protected $table = 'event_category_map';

    protected $fillable = [
        'event_id',
        'category_id',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }
}
