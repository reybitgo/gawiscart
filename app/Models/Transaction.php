<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'level',
        'source_order_id',
        'source_type',
        'amount',
        'status',
        'payment_method',
        'description',
        'admin_notes',
        'approved_by',
        'approved_at',
        'metadata',
        'reference_number'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'approved_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function sourceOrder()
    {
        return $this->belongsTo(\App\Models\Order::class, 'source_order_id');
    }

    /**
     * Get the source order number from metadata
     */
    public function getSourceOrderNumberAttribute()
    {
        return $this->metadata['order_number'] ?? null;
    }

    /**
     * Get the MLM level from transaction
     */
    public function getMLMLevelAttribute()
    {
        return $this->level;
    }

    /**
     * Check if this is an MLM commission transaction
     */
    public function isMLMCommission(): bool
    {
        return $this->type === 'mlm_commission' && $this->source_type === 'mlm';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->reference_number) {
                $transaction->reference_number = 'TXN-' . strtoupper(uniqid());
            }
        });
    }
}
