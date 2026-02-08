<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $table = 'payment_methods';
    protected $fillable = [
        'user_id',
        'method_type',
        'provider_name',
        'card_number',
        'account_holder_name',
        'expiry_date',
        'is_default',
    ];
    // App\Models\PaymentMethod.php

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
