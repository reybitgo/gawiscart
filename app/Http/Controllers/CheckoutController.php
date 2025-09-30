<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\WalletPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected WalletPaymentService $walletPaymentService;

    public function __construct(CartService $cartService, WalletPaymentService $walletPaymentService)
    {
        $this->cartService = $cartService;
        $this->walletPaymentService = $walletPaymentService;
    }

    /**
     * Show the checkout page
     */
    public function index()
    {
        $cartSummary = $this->cartService->getSummary();

        // Redirect if cart is empty
        if ($cartSummary['is_empty']) {
            return redirect()->route('packages.index')
                ->with('error', 'Your cart is empty. Please add some packages before checkout.');
        }

        // Validate cart items are still available
        $validationErrors = $this->cartService->validateCart();
        if (!empty($validationErrors)) {
            return redirect()->route('cart.index')
                ->with('error', 'Some items in your cart are no longer available. Please review your cart.');
        }

        // Get wallet payment summary
        $walletSummary = $this->walletPaymentService->getPaymentSummary(
            Auth::user(),
            $cartSummary['total']
        );

        return view('checkout.index', compact('cartSummary', 'walletSummary'));
    }

    /**
     * Process the checkout and create an order
     */
    public function process(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'delivery_method' => 'required|in:office_pickup,home_delivery',
            'customer_notes' => 'nullable|string|max:1000',
            'terms_accepted' => 'required|accepted',
            'payment_method' => 'required|in:wallet',
        ];

        // Add delivery address validation rules if home delivery is selected
        if ($request->delivery_method === 'home_delivery') {
            $validationRules = array_merge($validationRules, [
                'delivery_full_name' => 'required|string|max:255',
                'delivery_phone' => 'required|string|max:20',
                'delivery_address' => 'required|string|max:255',
                'delivery_address_2' => 'nullable|string|max:255',
                'delivery_city' => 'required|string|max:100',
                'delivery_state' => 'required|string|max:100',
                'delivery_zip' => 'required|string|max:20',
                'delivery_instructions' => 'nullable|string|max:1000',
                'delivery_time_preference' => 'nullable|in:anytime,morning,afternoon,weekend',
            ]);
        }

        $request->validate($validationRules);

        $cartSummary = $this->cartService->getSummary();

        // Check if cart is empty
        if ($cartSummary['is_empty']) {
            return redirect()->route('packages.index')
                ->with('error', 'Your cart is empty. Please add some packages before checkout.');
        }

        // Validate cart items one more time
        $validationErrors = $this->cartService->validateCart();
        if (!empty($validationErrors)) {
            return redirect()->route('cart.index')
                ->with('error', 'Some items in your cart are no longer available. Please review your cart.');
        }

        // Validate wallet payment
        $paymentValidation = $this->walletPaymentService->validatePayment(
            Auth::user(),
            $cartSummary['total']
        );

        if (!$paymentValidation['valid']) {
            return redirect()->route('checkout.index')
                ->with('error', $paymentValidation['message']);
        }

        try {
            DB::beginTransaction();

            // Create the order
            $order = Order::createFromCart(
                Auth::user(),
                $cartSummary,
                [
                    'checkout_timestamp' => now(),
                    'user_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'payment_method' => $request->payment_method,
                ]
            );

            // Prepare order update data
            $orderData = [
                'delivery_method' => $request->delivery_method,
                'customer_notes' => $request->customer_notes,
            ];

            // Add delivery address information for home delivery
            if ($request->delivery_method === 'home_delivery') {
                $orderData = array_merge($orderData, [
                    'delivery_address' => json_encode([
                        'full_name' => $request->delivery_full_name,
                        'phone' => $request->delivery_phone,
                        'address' => $request->delivery_address,
                        'address_2' => $request->delivery_address_2,
                        'city' => $request->delivery_city,
                        'state' => $request->delivery_state,
                        'zip' => $request->delivery_zip,
                        'instructions' => $request->delivery_instructions,
                        'time_preference' => $request->delivery_time_preference,
                    ])
                ]);

                // Update user's profile with delivery information for future use
                Auth::user()->update([
                    'fullname' => $request->delivery_full_name,
                    'phone' => $request->delivery_phone,
                    'address' => $request->delivery_address,
                    'address_2' => $request->delivery_address_2,
                    'city' => $request->delivery_city,
                    'state' => $request->delivery_state,
                    'zip' => $request->delivery_zip,
                    'delivery_instructions' => $request->delivery_instructions,
                    'delivery_time_preference' => $request->delivery_time_preference ?? 'anytime',
                ]);
            }

            $order->update($orderData);

            // Create order items from cart
            foreach ($cartSummary['items'] as $cartItem) {
                OrderItem::createFromCartItem($order, $cartItem);
            }

            // Reduce package quantities
            foreach ($cartSummary['items'] as $cartItem) {
                $package = \App\Models\Package::find($cartItem['package_id']);
                if ($package && $package->quantity_available !== null) {
                    $package->reduceQuantity($cartItem['quantity']);
                }
            }

            DB::commit();

            // Process wallet payment
            $paymentResult = $this->walletPaymentService->processPayment($order);

            if (!$paymentResult['success']) {
                // Rollback quantity changes if payment fails
                DB::beginTransaction();
                foreach ($cartSummary['items'] as $cartItem) {
                    $package = \App\Models\Package::find($cartItem['package_id']);
                    if ($package && $package->quantity_available !== null) {
                        $package->quantity_available += $cartItem['quantity'];
                        $package->save();
                    }
                }
                DB::commit();

                return redirect()->route('checkout.index')
                    ->with('error', 'Payment failed: ' . $paymentResult['message']);
            }

            // Clear the cart
            $this->cartService->clear();

            // Redirect to order confirmation
            return redirect()->route('checkout.confirmation', $order)
                ->with('success', 'Your order has been placed and paid successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error
            \Log::error('Checkout failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'cart_summary' => $cartSummary,
                'exception' => $e,
            ]);

            return redirect()->route('cart.index')
                ->with('error', 'There was an error processing your order. Please try again.');
        }
    }

    /**
     * Show order confirmation page
     */
    public function confirmation(Order $order)
    {
        // Ensure the order belongs to the current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'This order does not belong to you.');
        }

        // Load order items with package data
        $order->load(['orderItems.package']);

        return view('checkout.confirmation', compact('order'));
    }

    /**
     * Show order details page
     */
    public function orderDetails(Order $order)
    {
        // Ensure the order belongs to the current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'This order does not belong to you.');
        }

        // Load order items with package data
        $order->load(['orderItems.package']);

        return view('checkout.order-details', compact('order'));
    }

    /**
     * Cancel an order (if allowed)
     */
    public function cancelOrder(Request $request, Order $order)
    {
        // Ensure the order belongs to the current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'This order does not belong to you.');
        }

        // Check if order can be cancelled
        if (!$order->canBeCancelled()) {
            return redirect()->route('checkout.order-details', $order)
                ->with('error', 'This order cannot be cancelled at this time.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Process refund if order was paid
            $refundMessage = '';
            if ($order->isPaid()) {
                $refundResult = $this->walletPaymentService->refundPayment($order);
                if ($refundResult['success']) {
                    $refundMessage = ' Your wallet has been refunded.';
                } else {
                    throw new \Exception('Refund failed: ' . $refundResult['message']);
                }
            }

            // Cancel the order
            $order->cancel($request->cancellation_reason);

            // Restore package quantities
            foreach ($order->orderItems as $orderItem) {
                $package = $orderItem->package;
                if ($package && $package->quantity_available !== null) {
                    $package->quantity_available += $orderItem->quantity;
                    $package->save();
                }
            }

            DB::commit();

            return redirect()->route('checkout.order-details', $order)
                ->with('success', 'Your order has been cancelled successfully.' . $refundMessage);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Order cancellation failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'exception' => $e,
            ]);

            return redirect()->route('checkout.order-details', $order)
                ->with('error', 'There was an error cancelling your order. Please contact support.');
        }
    }

    /**
     * Get checkout summary for AJAX requests
     */
    public function getSummary()
    {
        $cartSummary = $this->cartService->getSummary();

        return response()->json([
            'success' => true,
            'summary' => $cartSummary,
            'validation_errors' => $this->cartService->validateCart(),
        ]);
    }
}