<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'reserved_balance',
        'is_active',
        'last_transaction_at'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'reserved_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'last_transaction_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class, 'user_id', 'user_id');
    }

    public function addBalance($amount)
    {
        $this->balance += $amount;
        $this->last_transaction_at = now();
        $this->save();
    }

    public function subtractBalance($amount)
    {
        if ($this->balance >= $amount) {
            $this->balance -= $amount;
            $this->last_transaction_at = now();
            $this->save();
            return true;
        }
        return false;
    }
}
