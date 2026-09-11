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
