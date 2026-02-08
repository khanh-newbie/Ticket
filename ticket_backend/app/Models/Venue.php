<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;
    protected $table = 'venues';
    protected $fillable = [
        'name',
        'city',
        'district',
        'ward',
        'street',
        
    ];
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
