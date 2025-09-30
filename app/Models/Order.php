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
        'delivery_method',
        'delivery_address',
        'subtotal',
        'tax_amount',
        'total_amount',
        'tax_rate',
        'points_awarded',
        'points_credited',
        'metadata',
        'notes',
        'customer_notes',
        'tracking_number',
        'courier_name',
        'pickup_date',
        'pickup_location',
        'pickup_instructions',
        'estimated_delivery',
        'admin_notes',
        'status_message',
        'paid_at',
        'processed_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'points_credited' => 'boolean',
        'pickup_date' => 'datetime',
        'estimated_delivery' => 'datetime',
        'paid_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4',
    ];

    // Delivery Methods
    const DELIVERY_OFFICE_PICKUP = 'office_pickup';
    const DELIVERY_HOME_DELIVERY = 'home_delivery';

    // Universal Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_PAYMENT_FAILED = 'payment_failed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PACKING = 'packing';

    // Office Pickup Statuses
    const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    const STATUS_PICKUP_NOTIFIED = 'pickup_notified';
    const STATUS_RECEIVED_IN_OFFICE = 'received_in_office';

    // Home Delivery Statuses
    const STATUS_READY_TO_SHIP = 'ready_to_ship';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_DELIVERY_FAILED = 'delivery_failed';

    // Common End Statuses
    const STATUS_COMPLETED = 'completed';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_RETURNED = 'returned';
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

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
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
    public function getDeliveryAddressAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        // Handle double JSON encoding
        $decoded = json_decode($value, true);

        // If the first decode returned a string (double encoded), decode again
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return $decoded;
    }

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
        // Generate a cryptographically secure random order number
        $date = now()->format('Ymd');
        // Use random_bytes for cryptographic security
        $randomBytes = random_bytes(4);
        $random = strtoupper(bin2hex($randomBytes));
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

    /**
     * Enhanced Status Management Methods
     */
    public static function getStatusLabels(): array
    {
        return [
            // Universal
            self::STATUS_PENDING => 'Pending Payment',
            self::STATUS_PAID => 'Payment Received',
            self::STATUS_PAYMENT_FAILED => 'Payment Failed',
            self::STATUS_PROCESSING => 'Processing Order',
            self::STATUS_CONFIRMED => 'Order Confirmed',
            self::STATUS_PACKING => 'Packing Items',

            // Office Pickup
            self::STATUS_READY_FOR_PICKUP => 'Ready for Pickup',
            self::STATUS_PICKUP_NOTIFIED => 'Pickup Notification Sent',
            self::STATUS_RECEIVED_IN_OFFICE => 'Collected from Office',

            // Home Delivery
            self::STATUS_READY_TO_SHIP => 'Ready for Shipment',
            self::STATUS_SHIPPED => 'Shipped',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_DELIVERY_FAILED => 'Delivery Failed',

            // Common
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_ON_HOLD => 'On Hold',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_RETURNED => 'Returned',
            self::STATUS_FAILED => 'Failed',
        ];
    }

    public function getStatusProgression(): array
    {
        $universal = [
            self::STATUS_PENDING,
            self::STATUS_PAID,
            self::STATUS_PROCESSING,
            self::STATUS_CONFIRMED,
            self::STATUS_PACKING
        ];

        if ($this->delivery_method === self::DELIVERY_OFFICE_PICKUP) {
            return array_merge($universal, [
                self::STATUS_READY_FOR_PICKUP,
                self::STATUS_PICKUP_NOTIFIED,
                self::STATUS_RECEIVED_IN_OFFICE,
                self::STATUS_COMPLETED
            ]);
        }

        return array_merge($universal, [
            self::STATUS_READY_TO_SHIP,
            self::STATUS_SHIPPED,
            self::STATUS_IN_TRANSIT,
            self::STATUS_OUT_FOR_DELIVERY,
            self::STATUS_DELIVERED,
            self::STATUS_COMPLETED
        ]);
    }

    public function getAllowedNextStatuses(): array
    {
        $transitions = [
            self::STATUS_PAID => [self::STATUS_PROCESSING, self::STATUS_ON_HOLD, self::STATUS_CANCELLED],
            self::STATUS_PROCESSING => [self::STATUS_CONFIRMED, self::STATUS_ON_HOLD, self::STATUS_CANCELLED],
            self::STATUS_CONFIRMED => [self::STATUS_PACKING, self::STATUS_ON_HOLD],
            self::STATUS_PACKING => $this->delivery_method === self::DELIVERY_OFFICE_PICKUP
                ? [self::STATUS_READY_FOR_PICKUP, self::STATUS_ON_HOLD]
                : [self::STATUS_READY_TO_SHIP, self::STATUS_ON_HOLD],

            // Office pickup transitions
            self::STATUS_READY_FOR_PICKUP => [self::STATUS_PICKUP_NOTIFIED, self::STATUS_ON_HOLD],
            self::STATUS_PICKUP_NOTIFIED => [self::STATUS_RECEIVED_IN_OFFICE, self::STATUS_ON_HOLD],
            self::STATUS_RECEIVED_IN_OFFICE => [self::STATUS_COMPLETED],

            // Home delivery transitions
            self::STATUS_READY_TO_SHIP => [self::STATUS_SHIPPED, self::STATUS_ON_HOLD],
            self::STATUS_SHIPPED => [self::STATUS_IN_TRANSIT, self::STATUS_RETURNED],
            self::STATUS_IN_TRANSIT => [self::STATUS_OUT_FOR_DELIVERY, self::STATUS_DELIVERED, self::STATUS_DELIVERY_FAILED],
            self::STATUS_OUT_FOR_DELIVERY => [self::STATUS_DELIVERED, self::STATUS_DELIVERY_FAILED],
            self::STATUS_DELIVERED => [self::STATUS_COMPLETED, self::STATUS_RETURNED],

            // Common transitions
            self::STATUS_ON_HOLD => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
            self::STATUS_DELIVERY_FAILED => [self::STATUS_OUT_FOR_DELIVERY, self::STATUS_RETURNED],
        ];

        return $transitions[$this->status] ?? [];
    }

    public function updateStatus(string $newStatus, ?string $notes = null, string $changedBy = 'admin'): bool
    {
        $oldStatus = $this->status;

        // Update the order status
        $this->update(['status' => $newStatus]);

        // Log the status change
        $this->statusHistory()->create([
            'status' => $newStatus,
            'notes' => $notes,
            'changed_by' => $changedBy,
            'metadata' => [
                'previous_status' => $oldStatus,
                'timestamp' => now()->toISOString(),
            ]
        ]);

        return true;
    }

    public function hasReachedStatus(string $status): bool
    {
        return $this->statusHistory()->where('status', $status)->exists() || $this->status === $status;
    }

    public function getStatusHistoryFor(string $status)
    {
        return $this->statusHistory()->where('status', $status)->first();
    }

    public function isOfficePickup(): bool
    {
        return $this->delivery_method === self::DELIVERY_OFFICE_PICKUP;
    }

    public function isHomeDelivery(): bool
    {
        return $this->delivery_method === self::DELIVERY_HOME_DELIVERY;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getDeliveryMethodLabelAttribute(): string
    {
        return $this->isOfficePickup() ? 'Office Pickup' : 'Home Delivery';
    }



    public static function getStatusGroups(): array
    {
        return [
            'pre_fulfillment' => [
                self::STATUS_PENDING,
                self::STATUS_PAID,
                self::STATUS_PROCESSING
            ],
            'fulfillment' => [
                self::STATUS_CONFIRMED,
                self::STATUS_PACKING,
                self::STATUS_READY_FOR_PICKUP,
                self::STATUS_READY_TO_SHIP
            ],
            'delivery' => [
                self::STATUS_PICKUP_NOTIFIED,
                self::STATUS_SHIPPED,
                self::STATUS_IN_TRANSIT,
                self::STATUS_OUT_FOR_DELIVERY,
                self::STATUS_DELIVERED,
                self::STATUS_RECEIVED_IN_OFFICE
            ],
            'issues' => [
                self::STATUS_ON_HOLD,
                self::STATUS_CANCELLED,
                self::STATUS_RETURNED,
                self::STATUS_REFUNDED,
                self::STATUS_DELIVERY_FAILED,
                self::STATUS_PAYMENT_FAILED,
                self::STATUS_FAILED
            ],
            'completed' => [
                self::STATUS_COMPLETED
            ]
        ];
    }

    public static function getStatusBadgeColor(string $status): string
    {
        return match($status) {
            self::STATUS_PENDING => 'secondary',
            self::STATUS_PAID => 'primary',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_CONFIRMED => 'success',
            self::STATUS_PACKING => 'info',
            self::STATUS_READY_FOR_PICKUP => 'warning',
            self::STATUS_READY_TO_SHIP => 'warning',
            self::STATUS_PICKUP_NOTIFIED => 'warning',
            self::STATUS_SHIPPED => 'primary',
            self::STATUS_IN_TRANSIT => 'primary',
            self::STATUS_OUT_FOR_DELIVERY => 'warning',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_RECEIVED_IN_OFFICE => 'success',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_ON_HOLD => 'warning',
            self::STATUS_CANCELLED => 'dark',
            self::STATUS_RETURNED => 'secondary',
            self::STATUS_REFUNDED => 'info',
            self::STATUS_DELIVERY_FAILED => 'danger',
            self::STATUS_PAYMENT_FAILED => 'danger',
            self::STATUS_FAILED => 'danger',
            default => 'secondary'
        };
    }
}