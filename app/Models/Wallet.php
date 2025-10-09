<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'is_active',
        'last_transaction_at',
        'mlm_balance',
        'purchase_balance',
    ];

    protected $casts = [
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

    /**
     * Add balance (deprecated - use addPurchaseBalance or addMLMIncome instead)
     * This method is kept for backward compatibility with withdrawal rejections
     */
    public function addBalance($amount)
    {
        // For backward compatibility, add to purchase balance
        $this->addPurchaseBalance($amount);
    }

    /**
     * Subtract balance (deprecated - use deductCombinedBalance instead)
     * This method is kept for backward compatibility
     */
    public function subtractBalance($amount)
    {
        return $this->deductCombinedBalance($amount);
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

    /**
     * Deduct from combined balance (purchase first, then MLM)
     * Used for package purchases
     */
    public function deductCombinedBalance(float $amount): bool
    {
        if ($this->total_balance < $amount) {
            return false;
        }

        \DB::beginTransaction();
        try {
            $remaining = $amount;

            // Deduct from purchase balance first
            if ($this->purchase_balance > 0) {
                $purchaseDeduction = min($this->purchase_balance, $remaining);
                $this->decrement('purchase_balance', $purchaseDeduction);
                $remaining -= $purchaseDeduction;
            }

            // Deduct remaining from MLM balance if needed
            if ($remaining > 0 && $this->mlm_balance >= $remaining) {
                $this->decrement('mlm_balance', $remaining);
            }

            $this->update(['last_transaction_at' => now()]);
            \DB::commit();
            return true;

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to deduct combined balance', [
                'wallet_id' => $this->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get MLM balance summary
     */
    public function getMLMBalanceSummary(): array
    {
        return [
            'mlm_balance' => (float) $this->mlm_balance,
            'purchase_balance' => (float) $this->purchase_balance,
            'total_balance' => $this->total_balance,
            'withdrawable_balance' => $this->withdrawable_balance
        ];
    }

    /**
     * Check if user can withdraw specific amount
     */
    public function canWithdraw(float $amount): bool
    {
        return $this->mlm_balance >= $amount;
    }
}
