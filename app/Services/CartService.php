<?php

namespace App\Services;

use App\Models\Package;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Session;

class CartService
{
    const CART_SESSION_KEY = 'shopping_cart';

    /**
     * Get the current tax rate from system settings
     */
    private function getTaxRate(): float
    {
        return SystemSetting::get('tax_rate', 0.07); // Default to 7% if not set
    }

    /**
     * Get all cart items
     */
    public function getItems(): array
    {
        $items = Session::get(self::CART_SESSION_KEY, []);

        // Migrate old cart items that don't have short_description
        $needsUpdate = false;
        foreach ($items as $packageId => &$item) {
            if (!isset($item['short_description'])) {
                $package = Package::find($packageId);
                if ($package) {
                    $item['short_description'] = $package->short_description;
                    $needsUpdate = true;
                }
            }
        }

        // Update session if any items were migrated
        if ($needsUpdate) {
            Session::put(self::CART_SESSION_KEY, $items);
        }

        return $items;
    }

    /**
     * Add item to cart
     */
    public function addItem(Package $package, int $quantity = 1): bool
    {
        if (!$package->isAvailable()) {
            return false;
        }

        $cart = $this->getItems();
        $packageId = $package->id;

        if (isset($cart[$packageId])) {
            $newQuantity = $cart[$packageId]['quantity'] + $quantity;

            // Check if new quantity exceeds available stock
            if ($package->quantity_available !== null && $newQuantity > $package->quantity_available) {
                return false;
            }

            $cart[$packageId]['quantity'] = $newQuantity;
        } else {
            // Check if quantity exceeds available stock
            if ($package->quantity_available !== null && $quantity > $package->quantity_available) {
                return false;
            }

            $cart[$packageId] = [
                'package_id' => $package->id,
                'name' => $package->name,
                'slug' => $package->slug,
                'price' => $package->price,
                'points_awarded' => $package->points_awarded,
                'image_url' => $package->image_url,
                'short_description' => $package->short_description,
                'quantity' => $quantity,
                'added_at' => now()->toISOString()
            ];
        }

        Session::put(self::CART_SESSION_KEY, $cart);
        return true;
    }

    /**
     * Update item quantity in cart
     */
    public function updateQuantity(int $packageId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->removeItem($packageId);
        }

        $cart = $this->getItems();

        if (!isset($cart[$packageId])) {
            return false;
        }

        // Check if package still exists and is available
        $package = Package::find($packageId);
        if (!$package || !$package->isAvailable()) {
            return false;
        }

        // Check quantity availability
        if ($package->quantity_available !== null && $quantity > $package->quantity_available) {
            return false;
        }

        $cart[$packageId]['quantity'] = $quantity;
        Session::put(self::CART_SESSION_KEY, $cart);
        return true;
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $packageId): bool
    {
        $cart = $this->getItems();

        if (isset($cart[$packageId])) {
            unset($cart[$packageId]);
            Session::put(self::CART_SESSION_KEY, $cart);
            return true;
        }

        return false;
    }

    /**
     * Clear entire cart
     */
    public function clear(): void
    {
        Session::forget(self::CART_SESSION_KEY);
    }

    /**
     * Get cart item count
     */
    public function getItemCount(): int
    {
        $cart = $this->getItems();
        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * Get cart subtotal
     */
    public function getSubtotal(): float
    {
        $cart = $this->getItems();
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        return round($subtotal, 2);
    }

    /**
     * Get cart tax amount
     */
    public function getTaxAmount(): float
    {
        return round($this->getSubtotal() * $this->getTaxRate(), 2);
    }

    /**
     * Get cart total
     */
    public function getTotal(): float
    {
        return round($this->getSubtotal() + $this->getTaxAmount(), 2);
    }

    /**
     * Get total points that will be awarded
     */
    public function getTotalPoints(): int
    {
        $cart = $this->getItems();
        $totalPoints = 0;

        foreach ($cart as $item) {
            $totalPoints += $item['points_awarded'] * $item['quantity'];
        }

        return $totalPoints;
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->getItems());
    }

    /**
     * Get cart summary for display
     */
    public function getSummary(): array
    {
        $taxAmount = $this->getTaxAmount();
        $taxRate = $this->getTaxRate();

        return [
            'items' => $this->getItems(),
            'item_count' => $this->getItemCount(),
            'subtotal' => $this->getSubtotal(),
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'show_tax' => $taxRate > 0, // Only show tax if rate is greater than 0
            'total' => $this->getTotal(),
            'total_points' => $this->getTotalPoints(),
            'is_empty' => $this->isEmpty()
        ];
    }

    /**
     * Validate cart items against current package availability
     */
    public function validateCart(): array
    {
        $cart = $this->getItems();
        $issues = [];
        $updatedCart = [];

        foreach ($cart as $packageId => $item) {
            $package = Package::find($packageId);

            if (!$package || !$package->isAvailable()) {
                $issues[] = "'{$item['name']}' is no longer available and has been removed from your cart.";
                continue;
            }

            // Check quantity availability
            if ($package->quantity_available !== null && $item['quantity'] > $package->quantity_available) {
                $issues[] = "Only {$package->quantity_available} units of '{$package->name}' are available. Cart quantity has been adjusted.";
                $item['quantity'] = $package->quantity_available;
            }

            // Update item data in case package details changed
            $item['name'] = $package->name;
            $item['price'] = $package->price;
            $item['points_awarded'] = $package->points_awarded;
            $item['image_url'] = $package->image_url;

            $updatedCart[$packageId] = $item;
        }

        // Update cart with validated items
        Session::put(self::CART_SESSION_KEY, $updatedCart);

        return $issues;
    }

    /**
     * Get cart for checkout (validates and returns clean data)
     */
    public function getCartForCheckout(): array
    {
        $this->validateCart();
        return $this->getSummary();
    }
}