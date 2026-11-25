<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'password',
        'role',
        'kode_grosir',
        'kode_otp',
        'photo_url',
        'email_verified_at',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'kode_otp'
    ];

    // protected function foto(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn($foto) => $foto ? asset('storage/' . $foto) : null,
    //     );
    // }

    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn($url) => $url ? asset('storage/' . $url) : null,
        );
    }

    protected function points(): Attribute
    {
        return Attribute::make(
            get: fn() => (int) $this->pointTransactions()->sum('amount'),
        );
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function alamats(): HasMany
    {
        return $this->hasMany(Alamat::class);
    }

    public function keranjangs()
    {
        return $this->hasMany(Keranjang::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function pushNotifications(): HasMany
    {
        return $this->hasMany(PushNotification::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function rewardRedemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }
}
