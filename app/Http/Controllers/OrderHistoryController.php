<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\WalletPaymentService;
use App\Mail\OrderCancelled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderHistoryController extends Controller
{
    protected WalletPaymentService $walletPaymentService;

    public function __construct(WalletPaymentService $walletPaymentService)
    {
        $this->walletPaymentService = $walletPaymentService;
    }

    /**
     * Display the order history for the authenticated user
     */
    public function index(Request $request)
    {
        $query = Order::with(['orderItems.package'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        if (in_array($sortBy, ['created_at', 'total_amount', 'status', 'payment_status', 'order_number'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $orders = $query->paginate(10)->withQueryString();

        // Get summary statistics
        $stats = $this->getOrderStats();

        return view('orders.index', compact('orders', 'stats'));
    }

    /**
     * Show a specific order details
     */
    public function show(Order $order)
    {
        // Ensure the order belongs to the current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'This order does not belong to you.');
        }

        // Load order items with package data
        $order->load(['orderItems.package']);

        return view('orders.show', compact('order'));
    }

    /**
     * Cancel an order
     */
    public function cancel(Request $request, Order $order)
    {
        // Ensure the order belongs to the current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'This order does not belong to you.');
        }

        // Check if order can be cancelled
        if (!$order->canBeCancelled()) {
            return redirect()->route('orders.show', $order)
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

            // Send cancellation email notification
            $this->sendCancellationEmail($order, $request->cancellation_reason, $refundProcessed);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Your order has been cancelled successfully.' . $refundMessage);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Order cancellation failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'exception' => $e,
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', 'There was an error cancelling your order. Please contact support.');
        }
    }

    /**
     * Reorder - create a new order from an existing order
     */
    public function reorder(Order $order)
    {
        // Ensure the order belongs to the current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'This order does not belong to you.');
        }

        $cartService = app('App\Services\CartService');

        try {
            // Add all order items to cart
            $addedItems = 0;
            $unavailableItems = [];

            foreach ($order->orderItems as $orderItem) {
                $package = $orderItem->package;

                if ($package && $package->isAvailable()) {
                    // Check if we can add the full quantity
                    $availableQuantity = $package->quantity_available ?? $orderItem->quantity;
                    $quantityToAdd = min($orderItem->quantity, $availableQuantity);

                    if ($quantityToAdd > 0) {
                        $cartService->addItem($package, $quantityToAdd);
                        $addedItems++;
                    }

                    if ($quantityToAdd < $orderItem->quantity) {
                        $unavailableItems[] = [
                            'name' => $orderItem->package_name,
                            'requested' => $orderItem->quantity,
                            'available' => $quantityToAdd,
                        ];
                    }
                } else {
                    $unavailableItems[] = [
                        'name' => $orderItem->package_name,
                        'requested' => $orderItem->quantity,
                        'available' => 0,
                    ];
                }
            }

            if ($addedItems > 0) {
                $message = "Added {$addedItems} items to your cart.";

                if (!empty($unavailableItems)) {
                    $message .= ' Some items were unavailable or had limited stock.';
                }

                return redirect()->route('cart.index')
                    ->with('success', $message)
                    ->with('unavailable_items', $unavailableItems);
            } else {
                return redirect()->route('orders.show', $order)
                    ->with('error', 'None of the items from this order are currently available.');
            }

        } catch (\Exception $e) {
            \Log::error('Reorder failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'exception' => $e,
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', 'There was an error processing your reorder. Please try again.');
        }
    }

    /**
     * Download order invoice/receipt
     */
    public function invoice(Order $order)
    {
        // Ensure the order belongs to the current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'This order does not belong to you.');
        }

        // Only allow invoice download for paid orders
        if (!$order->isPaid()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Invoice is only available for paid orders.');
        }

        return view('orders.invoice', compact('order'));
    }

    /**
     * Get order statistics for the current user
     */
    private function getOrderStats()
    {
        $userId = Auth::id();

        return [
            'total_orders' => Order::where('user_id', $userId)->count(),
            'pending_orders' => Order::where('user_id', $userId)->where('status', Order::STATUS_PENDING)->count(),
            'paid_orders' => Order::where('user_id', $userId)->where('payment_status', Order::PAYMENT_STATUS_PAID)->count(),
            'cancelled_orders' => Order::where('user_id', $userId)->where('status', Order::STATUS_CANCELLED)->count(),
            'total_spent' => Order::where('user_id', $userId)
                ->where('payment_status', Order::PAYMENT_STATUS_PAID)
                ->sum('total_amount'),
            'total_points_earned' => Order::where('user_id', $userId)
                ->where('points_credited', true)
                ->sum('points_awarded'),
        ];
    }

    /**
     * Get filtered orders for AJAX requests
     */
    public function ajax(Request $request)
    {
        $query = Order::with(['orderItems.package'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Apply filters (same as index method)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_notes', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(10)->withQueryString();

        return response()->json([
            'success' => true,
            'html' => view('orders.partials.order-list', compact('orders'))->render(),
            'pagination' => $orders->links()->render(),
        ]);
    }

    /**
     * Send cancellation email notification to customer
     */
    private function sendCancellationEmail(Order $order, ?string $reason, bool $refundProcessed): void
    {
        try {
            // Load the user relationship if not already loaded
            if (!$order->relationLoaded('user')) {
                $order->load('user');
            }

            // Send cancellation notification email
            Mail::to($order->user->email)->send(
                new OrderCancelled($order, $reason, $refundProcessed)
            );

            \Log::info('Order cancellation email sent', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'recipient' => $order->user->email,
                'refund_processed' => $refundProcessed
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send order cancellation email', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'recipient' => $order->user->email ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }
}
