<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organizer extends Model
{
    use HasFactory;
    const NOTVERIFIED = 0;
    const VERIFIED = 1;

    protected $table = 'organizers';
    protected $fillable = [
        'user_id',
        'organization_name',
        'description',
        'website',
        'verified',
        'logo',
    ];

    public function user()
{
    return $this->belongsTo(User::class);
}

public function events()
{
    return $this->hasMany(Event::class);
}
}
