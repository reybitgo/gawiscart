<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'payment_status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'tax_rate',
        'points_awarded',
        'points_credited',
        'metadata',
        'notes',
        'customer_notes',
        'paid_at',
        'processed_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'points_credited' => 'boolean',
        'paid_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Accessors & Mutators
     */
    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getFormattedTaxAmountAttribute(): string
    {
        return '$' . number_format($this->tax_amount, 2);
    }

    public function getTaxPercentageAttribute(): string
    {
        return number_format($this->tax_rate * 100, 1) . '%';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge bg-warning',
            self::STATUS_PAID => 'badge bg-info',
            self::STATUS_PROCESSING => 'badge bg-primary',
            self::STATUS_COMPLETED => 'badge bg-success',
            self::STATUS_CANCELLED => 'badge bg-secondary',
            self::STATUS_FAILED => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return match($this->payment_status) {
            self::PAYMENT_STATUS_PENDING => 'badge bg-warning',
            self::PAYMENT_STATUS_PAID => 'badge bg-success',
            self::PAYMENT_STATUS_FAILED => 'badge bg-danger',
            self::PAYMENT_STATUS_REFUNDED => 'badge bg-info',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Business Logic Methods
     */
    public function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(6));
        return "ORD-{$date}-{$random}";
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PAID]);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_PAID,
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'processed_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function cancel(string $reason = null): void
    {
        $metadata = $this->metadata ?? [];
        if ($reason) {
            $metadata['cancellation_reason'] = $reason;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    public function getTotalItemsCount(): int
    {
        return $this->orderItems->sum('quantity');
    }

    public function creditPoints(): bool
    {
        if ($this->points_credited || !$this->isPaid()) {
            return false;
        }

        // In Phase 4, this will integrate with the wallet system
        // For now, just mark as credited
        $this->update(['points_credited' => true]);

        return true;
    }

    /**
     * Static helper methods
     */
    public static function createFromCart(User $user, array $cartSummary, array $metadata = []): self
    {
        $order = new self();
        $order->user_id = $user->id;
        $order->order_number = $order->generateOrderNumber();
        $order->subtotal = $cartSummary['subtotal'];
        $order->tax_amount = $cartSummary['tax_amount'];
        $order->total_amount = $cartSummary['total'];
        $order->tax_rate = $cartSummary['tax_rate'] ?? 0;
        $order->points_awarded = $cartSummary['total_points'];
        $order->metadata = array_merge([
            'cart_snapshot' => $cartSummary,
            'created_via' => 'checkout',
        ], $metadata);

        $order->save();

        return $order;
    }
}