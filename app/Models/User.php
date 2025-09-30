<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'fullname',
        'email',
        'password',
        'phone',
        'address',
        'address_2',
        'city',
        'state',
        'zip',
        'delivery_instructions',
        'delivery_time_preference',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function wallet()
    {
        return $this->hasOne(\App\Models\Wallet::class);
    }

    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class);
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function getOrCreateWallet()
    {
        return $this->wallet ?: $this->wallet()->create(['user_id' => $this->id]);
    }

    /**
     * Determine if the user must verify their email address.
     *
     * @return bool
     */
    public function mustVerifyEmail()
    {
        // Check if email verification is enabled globally
        $emailVerificationEnabled = \App\Models\SystemSetting::get('email_verification_enabled', true);

        // If disabled globally, no verification required
        if (!$emailVerificationEnabled) {
            return false;
        }

        // If enabled, use Laravel's default behavior
        return !$this->hasVerifiedEmail();
    }

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }
}
