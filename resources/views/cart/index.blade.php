@extends('layouts.admin')

@section('title', 'Shopping Cart')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-2">Shopping Cart</h1>
                    <p class="text-muted">Review your selected packages</p>
                </div>
                <div>
                    <a href="{{ route('packages.index') }}" class="btn btn-outline-primary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                        </svg>
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(count($validationIssues) > 0)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <svg class="icon me-2">
                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
            </svg>
            <strong>Cart Updated:</strong>
            <ul class="mb-0 mt-2">
                @foreach($validationIssues as $issue)
                    <li>{{ $issue }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($cartSummary['is_empty'])
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body p-5">
                        <svg class="icon icon-5xl text-muted mb-4">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                        </svg>
                        <h3 class="mb-3">Your cart is empty</h3>
                        <p class="text-muted mb-4">Add some packages to your cart to get started.</p>
                        <a href="{{ route('packages.index') }}" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                            </svg>
                            Browse Packages
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Cart Items ({{ $cartSummary['item_count'] }} items)</h5>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCart()">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                            </svg>
                            Clear Cart
                        </button>
                    </div>
                    <div class="card-body p-0">
                        @foreach($cartSummary['items'] as $item)
                            <div class="cart-item border-bottom p-4" data-package-id="{{ $item['package_id'] }}">
                                <!-- Desktop Layout -->
                                <div class="d-none d-lg-flex align-items-center">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0 me-3">
                                        <img src="{{ $item['image_url'] }}"
                                             alt="{{ $item['name'] }}"
                                             class="rounded"
                                             style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>

                                    <!-- Product Info -->
                                    <div class="flex-grow-1 me-4">
                                        <h6 class="mb-1">
                                            <a href="{{ route('packages.show', $item['slug']) }}" class="text-decoration-none">
                                                {{ $item['name'] }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">{{ $item['points_awarded'] }} points each</small>
                                    </div>

                                    <!-- Unit Price -->
                                    <div class="text-center me-4" style="min-width: 80px;">
                                        <div class="fw-semibold">${{ number_format($item['price'], 2) }}</div>
                                        <small class="text-muted">each</small>
                                    </div>

                                    <!-- Quantity Controls -->
                                    <div class="me-4" style="min-width: 140px;">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary quantity-btn"
                                                    id="decrease-{{ $item['package_id'] }}"
                                                    onclick="updateQuantityWithLoader(this, {{ $item['package_id'] }}, {{ $item['quantity'] - 1 }})">
                                                -
                                            </button>
                                            <span class="mx-3 fw-semibold">{{ $item['quantity'] }}</span>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary quantity-btn"
                                                    id="increase-{{ $item['package_id'] }}"
                                                    onclick="updateQuantityWithLoader(this, {{ $item['package_id'] }}, {{ $item['quantity'] + 1 }})">
                                                +
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Total Price & Delete -->
                                    <div class="d-flex align-items-center" style="min-width: 140px;">
                                        <div class="fw-bold text-end me-3">${{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem({{ $item['package_id'] }})" title="Remove item">
                                            <svg class="icon">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Mobile Layout -->
                                <div class="d-lg-none">
                                    <div class="row">
                                        <!-- Product Image & Info -->
                                        <div class="col-8">
                                            <div class="d-flex">
                                                <img src="{{ $item['image_url'] }}"
                                                     alt="{{ $item['name'] }}"
                                                     class="rounded me-3 flex-shrink-0"
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('packages.show', $item['slug']) }}" class="text-decoration-none">
                                                            {{ $item['name'] }}
                                                        </a>
                                                    </h6>
                                                    <div class="text-muted small">{{ $item['points_awarded'] }} points each</div>
                                                    <div class="fw-semibold">${{ number_format($item['price'], 2) }} each</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Total Price & Delete -->
                                        <div class="col-4 text-end">
                                            <div class="fw-bold mb-2">${{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem({{ $item['package_id'] }})" title="Remove item">
                                                <svg class="icon">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Quantity Controls (Mobile) -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="text-muted">Quantity:</span>
                                                <div class="d-flex align-items-center">
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-secondary quantity-btn"
                                                            id="mobile-decrease-{{ $item['package_id'] }}"
                                                            onclick="updateQuantityWithLoader(this, {{ $item['package_id'] }}, {{ $item['quantity'] - 1 }})">
                                                        -
                                                    </button>
                                                    <span class="mx-3 fw-semibold">{{ $item['quantity'] }}</span>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-secondary quantity-btn"
                                                            id="mobile-increase-{{ $item['package_id'] }}"
                                                            onclick="updateQuantityWithLoader(this, {{ $item['package_id'] }}, {{ $item['quantity'] + 1 }})">
                                                        +
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card sticky-top">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal ({{ $cartSummary['item_count'] }} items)</span>
                            <span>${{ number_format($cartSummary['subtotal'], 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax</span>
                            <span>${{ number_format($cartSummary['tax_amount'], 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total</strong>
                            <strong class="text-primary">${{ number_format($cartSummary['total'], 2) }}</strong>
                        </div>
                        <div class="alert alert-info">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                            </svg>
                            You will earn <strong>{{ number_format($cartSummary['total_points']) }} points</strong> from this order!
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-lg" disabled>
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                                </svg>
                                Proceed to Checkout
                            </button>
                            <small class="text-muted text-center">
                                Checkout will be available in Phase 3
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Clear Cart Confirmation Modal -->
<div class="modal fade" id="clearCartModal" tabindex="-1" aria-labelledby="clearCartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearCartModalLabel">
                    <svg class="icon me-2 text-warning">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                    </svg>
                    Clear Cart
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <svg class="icon icon-2xl text-warning me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                    </svg>
                    <div>
                        <h6 class="mb-1">Are you sure you want to clear your entire cart?</h6>
                        <p class="text-muted mb-0 small">This action cannot be undone. All items will be removed from your cart.</p>
                    </div>
                </div>
                <div class="alert alert-warning mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                    </svg>
                    <strong>Note:</strong> You will need to add items back to your cart manually if you proceed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                    </svg>
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmClearCart">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                    </svg>
                    Clear Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Item Confirmation Modal -->
<div class="modal fade" id="removeItemModal" tabindex="-1" aria-labelledby="removeItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeItemModalLabel">
                    <svg class="icon me-2 text-warning">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                    </svg>
                    Remove Item
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <svg class="icon icon-2xl text-danger me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                    </svg>
                    <div>
                        <h6 class="mb-1">Are you sure you want to remove this item from your cart?</h6>
                        <p class="text-muted mb-0 small" id="removeItemDetails">This item will be completely removed from your cart.</p>
                    </div>
                </div>
                <div class="card border-warning">
                    <div class="card-body p-3" id="removeItemPreview">
                        <!-- Item details will be populated here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                    </svg>
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmRemoveItem">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                    </svg>
                    Remove Item
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .quantity-btn:disabled {
        opacity: 0.7;
    }

    .quantity-btn .spinner-border-sm {
        width: 0.75rem;
        height: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
    function showMessage(message, type = 'info') {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
        `;

        document.body.appendChild(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }
    async function updateQuantityWithLoader(buttonElement, packageId, newQuantity) {
        if (newQuantity < 0) return;

        // Store original button content
        const originalContent = buttonElement.innerHTML;
        const originalDisabled = buttonElement.disabled;

        // Show loading spinner
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            await updateQuantity(packageId, newQuantity);
        } catch (error) {
            // Restore button state on error
            buttonElement.disabled = originalDisabled;
            buttonElement.innerHTML = originalContent;
            throw error;
        }
    }

    async function updateQuantity(packageId, newQuantity) {
        if (newQuantity < 0) return;

        console.log('Updating quantity:', { packageId, newQuantity });

        try {
            const url = window.cartRoutes.update.replace('{packageId}', packageId);
            console.log('Update URL:', url);

            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ quantity: newQuantity })
            });

            console.log('Response status:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('HTTP error response:', errorText);
                showMessage('Server error: ' + response.status, 'error');
                return;
            }

            const data = await response.json();
            console.log('Response data:', data);

            if (data.success) {
                location.reload(); // Reload to update the cart display
            } else {
                showMessage(data.message, 'error');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            showMessage('Error updating cart: ' + error.message, 'error');
        }
    }

    let currentRemovePackageId = null;

    function removeItem(packageId) {
        console.log('removeItem called with packageId:', packageId);

        // Store the package ID for later use
        currentRemovePackageId = packageId;

        // Get item details from the cart row
        const cartItem = document.querySelector(`[data-package-id="${packageId}"]`);
        console.log('Found cart item:', cartItem);

        if (!cartItem) {
            console.error('Cart item not found for package ID:', packageId);
            return;
        }

        const itemName = cartItem.querySelector('h6 a').textContent.trim();
        const itemImage = cartItem.querySelector('img').src;
        const itemPrice = cartItem.querySelector('.fw-semibold').textContent.trim();
        const quantitySpan = cartItem.querySelector('.mx-3.fw-semibold');
        const itemQuantity = quantitySpan ? quantitySpan.textContent.trim() : '1';

        console.log('Item details:', { itemName, itemImage, itemPrice, itemQuantity });

        // Populate the modal with item details
        document.getElementById('removeItemPreview').innerHTML = `
            <div class="d-flex align-items-center">
                <img src="${itemImage}" alt="${itemName}" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="flex-grow-1">
                    <div class="fw-semibold">${itemName}</div>
                    <div class="text-muted small">${itemQuantity} × ${itemPrice}</div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-danger">Remove</div>
                </div>
            </div>
        `;

        // Show the modal
        const modalElement = document.getElementById('removeItemModal');
        console.log('Modal element found:', modalElement);

        if (modalElement) {
            const modal = new coreui.Modal(modalElement);
            console.log('Modal instance created:', modal);
            modal.show();
            console.log('Modal.show() called');
        } else {
            console.error('Remove item modal not found in DOM');
        }
    }

    async function performRemoveItem() {
        if (!currentRemovePackageId) return;

        try {
            // Hide the modal first
            const modal = coreui.Modal.getInstance(document.getElementById('removeItemModal'));
            modal.hide();

            // Show loading state on the confirm button
            const confirmBtn = document.getElementById('confirmRemoveItem');
            const originalText = confirmBtn.innerHTML;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Removing...';

            const url = window.cartRoutes.remove.replace('{packageId}', currentRemovePackageId);
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                showMessage('Item removed from cart', 'success');
                location.reload(); // Reload to update the cart display
            } else {
                showMessage(data.message, 'error');
                // Reset button state
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error removing item:', error);
            showMessage('Error removing item', 'error');
            // Reset button state
            const confirmBtn = document.getElementById('confirmRemoveItem');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        }

        // Clear the stored package ID
        currentRemovePackageId = null;
    }

    function clearCart() {
        // Show the modal instead of using confirm
        const modal = new coreui.Modal(document.getElementById('clearCartModal'));
        modal.show();
    }

    async function performClearCart() {
        try {
            // Hide the modal first
            const modal = coreui.Modal.getInstance(document.getElementById('clearCartModal'));
            modal.hide();

            // Show loading state on the confirm button
            const confirmBtn = document.getElementById('confirmClearCart');
            const originalText = confirmBtn.innerHTML;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Clearing...';

            const response = await fetch(window.cartRoutes.clear, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                showMessage('Cart cleared successfully', 'success');
                location.reload(); // Reload to show empty cart
            } else {
                showMessage('Error clearing cart', 'error');
                // Reset button state
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error clearing cart:', error);
            showMessage('Error clearing cart', 'error');
            // Reset button state
            const confirmBtn = document.getElementById('confirmClearCart');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        }
    }

    // Bind the confirm button to actually clear the cart
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded event fired');

        const clearBtn = document.getElementById('confirmClearCart');
        const removeBtn = document.getElementById('confirmRemoveItem');

        console.log('Clear button found:', clearBtn);
        console.log('Remove button found:', removeBtn);

        if (clearBtn) {
            clearBtn.addEventListener('click', performClearCart);
            console.log('Clear cart event listener attached');
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', performRemoveItem);
            console.log('Remove item event listener attached');
        }

        // Test if CoreUI is available
        console.log('CoreUI available:', typeof coreui !== 'undefined');
        console.log('CoreUI Modal available:', typeof coreui.Modal !== 'undefined');
    });
</script>
@endpush
@endsection