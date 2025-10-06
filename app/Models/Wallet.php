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
        'last_transaction_at',
        'mlm_balance',
        'purchase_balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'reserved_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'last_transaction_at' => 'datetime',
        'mlm_balance' => 'decimal:2',
        'purchase_balance' => 'decimal:2',
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

    /**
     * Get total available balance (MLM + Purchase)
     */
    public function getTotalBalanceAttribute(): float
    {
        return (float) ($this->mlm_balance + $this->purchase_balance);
    }

    /**
     * Get withdrawable balance (MLM only)
     */
    public function getWithdrawableBalanceAttribute(): float
    {
        return (float) $this->mlm_balance;
    }

    /**
     * Add MLM income to wallet
     */
    public function addMLMIncome(float $amount, string $description, int $level, int $sourceOrderId): bool
    {
        \DB::beginTransaction();
        try {
            $this->increment('mlm_balance', $amount);
            $this->update(['last_transaction_at' => now()]);

            Transaction::create([
                'wallet_id' => $this->id,
                'type' => 'mlm_commission',
                'amount' => $amount,
                'description' => $description,
                'status' => 'completed',
                'metadata' => json_encode([
                    'level' => $level,
                    'source_order_id' => $sourceOrderId
                ])
            ]);

            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to add MLM income', [
                'wallet_id' => $this->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Add purchase balance (deposits, transfers)
     */
    public function addPurchaseBalance(float $amount): void
    {
        $this->increment('purchase_balance', $amount);
        $this->update(['last_transaction_at' => now()]);
    }
}
