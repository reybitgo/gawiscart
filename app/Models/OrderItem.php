<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'package_id',
        'quantity',
        'unit_price',
        'total_price',
        'points_awarded_per_item',
        'total_points_awarded',
        'package_snapshot',
    ];

    protected $casts = [
        'package_snapshot' => 'array',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Accessors & Mutators
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return '$' . number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return '$' . number_format($this->total_price, 2);
    }

    public function getPackageNameAttribute(): string
    {
        // Try to get name from snapshot first, then fall back to current package
        if ($this->package_snapshot && isset($this->package_snapshot['name'])) {
            return $this->package_snapshot['name'];
        }

        return $this->package?->name ?? 'Unknown Package';
    }

    public function getPackageImageUrlAttribute(): string
    {
        // Try to get image URL from snapshot first, then fall back to current package
        if ($this->package_snapshot && isset($this->package_snapshot['image_url'])) {
            return $this->package_snapshot['image_url'];
        }

        return $this->package?->image_url ?? asset('images/package-placeholder.svg');
    }

    public function getPackageDescriptionAttribute(): string
    {
        // Try to get description from snapshot first, then fall back to current package
        if ($this->package_snapshot && isset($this->package_snapshot['short_description'])) {
            return $this->package_snapshot['short_description'];
        }

        return $this->package?->short_description ?? '';
    }

    /**
     * Business Logic Methods
     */
    public function calculateTotalPrice(): void
    {
        $this->total_price = $this->unit_price * $this->quantity;
        $this->total_points_awarded = $this->points_awarded_per_item * $this->quantity;
    }

    /**
     * Static helper methods
     */
    public static function createFromCartItem(Order $order, array $cartItem): self
    {
        $package = Package::find($cartItem['package_id']);

        if (!$package) {
            throw new \Exception("Package not found: {$cartItem['package_id']}");
        }

        $orderItem = new self();
        $orderItem->order_id = $order->id;
        $orderItem->package_id = $package->id;
        $orderItem->quantity = $cartItem['quantity'];
        $orderItem->unit_price = $cartItem['price'];
        $orderItem->points_awarded_per_item = $cartItem['points_awarded'];

        // Create package snapshot to preserve package details
        $orderItem->package_snapshot = [
            'name' => $package->name,
            'slug' => $package->slug,
            'short_description' => $package->short_description,
            'long_description' => $package->long_description,
            'image_url' => $package->image_url,
            'category' => $package->meta_data['category'] ?? null,
            'features' => $package->meta_data['features'] ?? [],
            'duration' => $package->meta_data['duration'] ?? null,
            'captured_at' => now()->toISOString(),
        ];

        $orderItem->calculateTotalPrice();
        $orderItem->save();

        return $orderItem;
    }

    /**
     * Check if the package still exists and is available
     */
    public function isPackageStillAvailable(): bool
    {
        return $this->package && $this->package->isAvailable();
    }

    /**
     * Get package information from snapshot or current package
     */
    public function getPackageInfo(): array
    {
        if ($this->package_snapshot) {
            return $this->package_snapshot;
        }

        if ($this->package) {
            return [
                'name' => $this->package->name,
                'slug' => $this->package->slug,
                'short_description' => $this->package->short_description,
                'image_url' => $this->package->image_url,
                'current_price' => $this->package->price,
                'is_available' => $this->package->isAvailable(),
            ];
        }

        return [
            'name' => 'Unknown Package',
            'slug' => null,
            'short_description' => 'This package is no longer available',
            'image_url' => asset('images/package-placeholder.svg'),
            'current_price' => null,
            'is_available' => false,
        ];
    }
}