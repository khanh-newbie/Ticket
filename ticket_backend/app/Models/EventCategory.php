<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    use HasFactory;
    protected $table = 'event_categories';
    /* 1 nhạc sống
    2 thể thao
    3 sân khấu nghệ thuật
    4 khác
    */
    protected $fillable = [
        'name',
    ];

    public function events()
    {
        return $this->hasMany(Event::class, 'category_id');
    }

    public function eventsMany()
    {
        return $this->belongsToMany(Event::class, 'event_category_map', 'category_id', 'event_id');
    }
}
