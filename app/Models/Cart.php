<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'currency',
    ];

    protected static function booted(): void
    {
        static::creating(function (Cart $cart) {
            if (empty($cart->session_id)) {
                $cart->session_id = $cart->user_id ? 'user_' . $cart->user_id : 'cart_' . \Illuminate\Support\Str::random(32);
            }
        });
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function forUser(int|string $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'session_id' => 'user_' . $userId,
                'currency'   => 'EGP',
            ]
        );
    }
}
