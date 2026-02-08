<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    const ROLE_CUSTOMER = 1;
    const ROLE_ORGANIZER = 2;
    const ROLE_ADMIN = 3;
    const STATUS_ACTIVE = 1;
    const STATUS_BANNED = 2;
    const STATUS_PENDING = 3;
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'avatar',
        'status',
        'device_token',
        'avatar',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function organizer()
{
    return $this->hasOne(Organizer::class);
}

public function verifications()
{
    return $this->hasMany(UserVerification::class);
}

// public function sentMessages()
// {
//     return $this->hasMany(Message::class, 'sender_id');
// }

// public function receivedMessages()
// {
//     return $this->hasMany(Message::class, 'receiver_id');
// }

// public function adminLogs()
// {
//     return $this->hasMany(AdminLog::class, 'admin_id');
// }

// public function reviews()
// {
//     return $this->hasMany(Review::class);
// }

public function carts()
{
    return $this->hasMany(Cart::class);
}

public function orders()
{
    return $this->hasMany(Order::class);
}

// public function paymentMethods()
// {
//     return $this->hasMany(PaymentMethod::class);
// }
}
