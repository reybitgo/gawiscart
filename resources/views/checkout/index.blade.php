@extends('layouts.admin')

@section('title', 'Checkout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-2">Checkout</h1>
                    <p class="text-muted">Review your order and complete your purchase</p>
                </div>
                <div>
                    <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                        </svg>
                        Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Review -->
        <div class="col-lg-8">
            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                        </svg>
                        Order Items ({{ $cartSummary['item_count'] }} items)
                    </h5>
                </div>
                <div class="card-body p-0">
                    @foreach($cartSummary['items'] as $item)
                        <div class="border-bottom p-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $item['name'] }}</h6>
                                    @if(isset($item['short_description']) && $item['short_description'])
                                        <p class="text-muted small mb-1">{{ Str::limit($item['short_description'], 80) }}</p>
                                    @endif
                                    <div class="d-flex align-items-center text-sm">
                                        <span class="me-3">Quantity: <strong>{{ $item['quantity'] }}</strong></span>
                                        <span class="me-3">Unit Price: <strong>${{ number_format($item['price'], 2) }}</strong></span>
                                        <span class="text-primary">Points: <strong>{{ number_format($item['points_awarded'] * $item['quantity']) }}</strong></span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold h5 mb-0">${{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Payment Method -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                        </svg>
                        Payment Method
                    </h5>
                </div>
                <div class="card-body">
                    <form id="checkout-form" action="{{ route('checkout.process') }}" method="POST">
                        @csrf

                        <!-- Wallet Payment Option -->
                        <div class="form-check border rounded p-3 mb-3 {{ $walletSummary['can_pay'] ? 'border-success' : 'border-danger' }}">
                            <input class="form-check-input @error('payment_method') is-invalid @enderror"
                                   type="radio"
                                   name="payment_method"
                                   id="wallet_payment"
                                   value="wallet"
                                   {{ $walletSummary['can_pay'] ? 'checked' : 'disabled' }}
                                   {{ old('payment_method') === 'wallet' ? 'checked' : '' }}>
                            <label class="form-check-label w-100" for="wallet_payment">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Pay with E-Wallet</strong>
                                        <div class="text-muted small">Use your wallet balance to complete this purchase</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="badge {{ $walletSummary['wallet_active'] ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $walletSummary['wallet_active'] ? 'Active' : 'Inactive' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Wallet Balance Information -->
                                <div class="mt-2">
                                    <div class="row text-sm">
                                        <div class="col-md-6">
                                            <span class="text-muted">Current Balance:</span>
                                            <strong class="{{ $walletSummary['can_pay'] ? 'text-success' : 'text-danger' }}">
                                                {{ $walletSummary['formatted_balance'] }}
                                            </strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted">Order Total:</span>
                                            <strong>{{ $walletSummary['formatted_order_amount'] }}</strong>
                                        </div>
                                    </div>
                                    @if($walletSummary['can_pay'])
                                        <div class="mt-2">
                                            <span class="text-muted">Remaining Balance:</span>
                                            <strong class="text-info">{{ $walletSummary['formatted_remaining_balance'] }}</strong>
                                        </div>
                                    @else
                                        <div class="mt-2">
                                            <div class="alert alert-danger small mb-0">
                                                <svg class="icon me-1">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                                                </svg>
                                                {{ $walletSummary['validation_message'] }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </label>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(!$walletSummary['can_pay'])
                            <div class="alert alert-warning">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                </svg>
                                <strong>Insufficient Balance:</strong> You need to add funds to your wallet before you can complete this purchase. Please contact support to add funds to your wallet.
                            </div>
                        @endif

                        <!-- Customer Notes -->
                        <div class="mb-3">
                            <label for="customer_notes" class="form-label">Special Instructions or Notes (Optional)</label>
                            <textarea class="form-control @error('customer_notes') is-invalid @enderror"
                                      id="customer_notes"
                                      name="customer_notes"
                                      rows="3"
                                      placeholder="Any special instructions or notes for your order...">{{ old('customer_notes') }}</textarea>
                            @error('customer_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maximum 1000 characters</div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-check mb-3">
                            <input class="form-check-input @error('terms_accepted') is-invalid @enderror"
                                   type="checkbox"
                                   id="terms_accepted"
                                   name="terms_accepted"
                                   value="1"
                                   {{ old('terms_accepted') ? 'checked' : '' }}>
                            <label class="form-check-label" for="terms_accepted">
                                I agree to the <a href="#" data-coreui-toggle="modal" data-coreui-target="#termsModal">Terms and Conditions</a> and <a href="#" data-coreui-toggle="modal" data-coreui-target="#privacyModal">Privacy Policy</a>
                            </label>
                            @error('terms_accepted')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card sticky-top sticky-order-summary">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-calculator') }}"></use>
                        </svg>
                        Order Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal ({{ $cartSummary['item_count'] }} items)</span>
                        <span>${{ number_format($cartSummary['subtotal'], 2) }}</span>
                    </div>
                    @if($cartSummary['show_tax'])
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax ({{ number_format($cartSummary['tax_rate'] * 100, 1) }}%)</span>
                        <span>${{ number_format($cartSummary['tax_amount'], 2) }}</span>
                    </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total</strong>
                        <strong class="text-primary h5">${{ number_format($cartSummary['total'], 2) }}</strong>
                    </div>

                    <!-- Points Summary -->
                    <div class="alert alert-info">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                        </svg>
                        You will earn <strong>{{ number_format($cartSummary['total_points']) }} points</strong> from this order!
                    </div>

                    <!-- Wallet Payment Summary -->
                    @if($walletSummary['can_pay'])
                        <div class="alert alert-success">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                            </svg>
                            <strong>Payment Method:</strong> E-Wallet<br>
                            <small>Your order will be paid immediately using your wallet balance.</small>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                            </svg>
                            <strong>Payment Required:</strong> Please ensure you have sufficient wallet balance to complete this purchase.
                        </div>
                    @endif

                    <!-- Place Order Button -->
                    <div class="d-grid">
                        <button type="submit"
                                form="checkout-form"
                                class="btn btn-primary btn-lg"
                                id="place-order-btn"
                                {{ !$walletSummary['can_pay'] ? 'disabled' : '' }}>
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                            </svg>
                            {{ $walletSummary['can_pay'] ? 'Pay Now' : 'Insufficient Balance' }}
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                            </svg>
                            Your order information is secure and protected
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@push('styles')
<style>
.sticky-order-summary {
    z-index: 100 !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('place-order-btn');
    const termsCheckbox = document.getElementById('terms_accepted');
    const paymentMethodRadio = document.getElementById('wallet_payment');
    const canPay = {{ $walletSummary['can_pay'] ? 'true' : 'false' }};

    // Enable/disable submit button based on validation
    function updateSubmitButton() {
        const termsAccepted = termsCheckbox.checked;
        const paymentSelected = paymentMethodRadio && paymentMethodRadio.checked;
        const hasValidPayment = canPay && paymentSelected;

        submitBtn.disabled = !(termsAccepted && hasValidPayment);
    }

    // Initial check
    updateSubmitButton();

    // Listen for checkbox changes
    if (termsCheckbox) {
        termsCheckbox.addEventListener('change', updateSubmitButton);
    }

    if (paymentMethodRadio) {
        paymentMethodRadio.addEventListener('change', updateSubmitButton);
    }

    // Handle form submission
    form.addEventListener('submit', function(e) {
        if (!termsCheckbox.checked) {
            e.preventDefault();
            alert('Please accept the terms and conditions to continue.');
            return;
        }

        if (!paymentMethodRadio || !paymentMethodRadio.checked) {
            e.preventDefault();
            alert('Please select a payment method to continue.');
            return;
        }

        if (!canPay) {
            e.preventDefault();
            alert('Insufficient wallet balance. Please add funds to your wallet.');
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </span>
            Processing Payment...
        `;
    });
});
</script>
@endpush
@include('legal.terms-of-service')
@include('legal.privacy-policy')
@endsection