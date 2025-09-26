@extends('layouts.admin')

@section('title', 'Withdraw Funds')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-minus') }}"></use>
                    </svg>
                    Withdraw Funds
                </h4>
                <p class="text-body-secondary mb-0">Transfer money from your e-wallet to your bank account</p>
            </div>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Balance Information Cards -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-primary-gradient text-white">
            <div class="card-body text-center">
                <h5 class="card-title">Wallet Balance</h5>
                <h2 class="display-5 fw-bold">${{ number_format($wallet->balance, 2) }}</h2>
                <p class="mb-0">
                    <span class="badge {{ $wallet->is_active ? 'bg-light text-success' : 'bg-warning text-dark' }}">
                        {{ $wallet->is_active ? 'Account Active' : 'Account Frozen' }}
                    </span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card {{ $availableBalance > 0 ? 'bg-success-gradient' : 'bg-warning-gradient' }} text-white">
            <div class="card-body text-center">
                <h5 class="card-title">Available for Withdrawal</h5>
                <h2 class="display-5 fw-bold">${{ number_format($availableBalance, 2) }}</h2>
                @if($pendingWithdrawals > 0)
                    <p class="mb-0 small">
                        <span class="badge bg-light text-dark">
                            ${{ number_format($pendingWithdrawals, 2) }} pending approval
                        </span>
                    </p>
                @else
                    <p class="mb-0 small">
                        <span class="badge bg-light text-success">
                            No pending withdrawals
                        </span>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Amount Buttons -->
<div class="row mb-4">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-speedometer') }}"></use>
                </svg>
                <strong>Quick Amounts</strong>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    @php
                        $quickAmounts = [5, 10, 25, 50, 100, 250, 500, 1000];
                        $hasQuickAmounts = false;
                    @endphp
                    @foreach($quickAmounts as $quickAmount)
                        @if($availableBalance >= $quickAmount)
                            @php $hasQuickAmounts = true; @endphp
                            <button type="button" class="btn btn-outline-danger" onclick="setAmount({{ $quickAmount }})">
                                ${{ $quickAmount }}
                            </button>
                        @endif
                    @endforeach

                    @if(!$hasQuickAmounts)
                        <div class="text-center text-muted">
                            <i class="icon-info me-2"></i>
                            No quick amounts available. Available balance: ${{ number_format($availableBalance, 2) }}
                            @if($pendingWithdrawals > 0)
                                <br><small>You have ${{ number_format($pendingWithdrawals, 2) }} in pending withdrawals</small>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
        </svg>
        {{ session('success') }}
        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
    </div>
@endif

<!-- Withdrawal Form -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-minus') }}"></use>
                </svg>
                <strong>Withdrawal Form</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('wallet.withdraw.process') }}" id="withdraw-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
                                    </svg>
                                    Withdrawal Amount
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="amount" id="amount" class="form-control"
                                           placeholder="0.00" min="1" max="{{ min($wallet->balance, 10000) }}" step="0.01" required
                                           value="{{ old('amount') }}">
                                    <span class="input-group-text">USD</span>
                                </div>
                                <div class="form-text">
                                    Minimum: $1.00 | Maximum: ${{ number_format(min($wallet->balance, 10000), 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                                    </svg>
                                    Payment Method
                                </label>
                                <select id="payment_method" name="payment_method" class="form-select" required>
                                    <option value="">Select payment method</option>
                                    @if($paymentSettings['gcash_enabled'])
                                        <option value="Gcash" {{ old('payment_method') == 'Gcash' ? 'selected' : '' }}>Gcash</option>
                                    @endif
                                    @if($paymentSettings['maya_enabled'])
                                        <option value="Maya" {{ old('payment_method') == 'Maya' ? 'selected' : '' }}>Maya</option>
                                    @endif
                                    @if($paymentSettings['cash_enabled'])
                                        <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                    @endif
                                    @if($paymentSettings['allow_others'])
                                        <option value="Others" {{ old('payment_method') == 'Others' ? 'selected' : '' }}>Others</option>
                                    @endif
                                </select>
                                <input type="text" id="custom_payment_method" name="custom_payment_method" class="form-control d-none mt-2"
                                       placeholder="Please type payment method" value="{{ old('custom_payment_method') }}">
                                <div class="form-text">
                                    Select your preferred withdrawal method
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fee Breakdown Section -->
                    @if($withdrawalFeeSettings['fee_enabled'])
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-calculator') }}"></use>
                                    </svg>
                                    <strong>Fee Breakdown</strong>
                                </div>
                                <div class="card-body">
                                    <div id="fee-breakdown" class="d-none">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="d-flex justify-content-between">
                                                    <span>Withdrawal Amount:</span>
                                                    <span id="withdrawal-amount-display">$0.00</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="d-flex justify-content-between">
                                                    <span>Processing Fee:</span>
                                                    <span id="fee-amount-display">$0.00</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="d-flex justify-content-between border-top pt-2">
                                                    <strong>Total Deducted:</strong>
                                                    <strong id="total-amount-display">$0.00</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <small class="text-muted">
                                                    Fee calculation:
                                                    @if($withdrawalFeeSettings['fee_type'] === 'percentage')
                                                        {{ $withdrawalFeeSettings['fee_value'] }}% of withdrawal amount
                                                        @if($withdrawalFeeSettings['minimum_fee'] > 0 || $withdrawalFeeSettings['maximum_fee'] < 999999)
                                                            (Min: ${{ number_format($withdrawalFeeSettings['minimum_fee'], 2) }}, Max: ${{ number_format($withdrawalFeeSettings['maximum_fee'], 2) }})
                                                        @endif
                                                    @else
                                                        Fixed fee of ${{ number_format($withdrawalFeeSettings['fee_value'], 2) }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="fee-prompt" class="text-center text-muted">
                                        <i class="icon-info me-2"></i>
                                        Enter a withdrawal amount to see the fee breakdown
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Gcash Details -->
                    <div id="gcash_details" class="row d-none mt-4">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="gcash_number" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-mobile') }}"></use>
                                    </svg>
                                    Your Gcash Number
                                </label>
                                <input type="text" name="gcash_number" id="gcash_number" class="form-control"
                                       placeholder="09171234567" maxlength="11"
                                       value="{{ old('gcash_number') }}">
                                <div class="form-text">
                                    Enter your Gcash mobile number where you want to receive the funds
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Maya Details -->
                    <div id="maya_details" class="row d-none mt-4">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="maya_number" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-mobile') }}"></use>
                                    </svg>
                                    Your Maya Number
                                </label>
                                <input type="text" name="maya_number" id="maya_number" class="form-control"
                                       placeholder="09171234567" maxlength="11"
                                       value="{{ old('maya_number') }}">
                                <div class="form-text">
                                    Enter your Maya mobile number where you want to receive the funds
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cash Pickup Details -->
                    <div id="cash_details" class="row d-none mt-4">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="pickup_location" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-location-pin') }}"></use>
                                    </svg>
                                    Preferred Pickup Location
                                </label>
                                <input type="text" name="pickup_location" id="pickup_location" class="form-control"
                                       placeholder="Enter your preferred cash pickup location"
                                       value="{{ old('pickup_location') }}">
                                <div class="form-text">
                                    Specify where you'd like to pick up your cash withdrawal
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other Payment Method Details -->
                    <div id="other_details" class="row d-none mt-4">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="payment_details" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                    </svg>
                                    Payment Details
                                </label>
                                <textarea name="payment_details" id="payment_details" class="form-control" rows="3"
                                          placeholder="Enter your payment method details (account numbers, instructions, etc.)">{{ old('payment_details') }}</textarea>
                                <div class="form-text">
                                    Provide detailed information about your payment method and account details
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Important Information -->
                    <div class="alert alert-warning mt-4">
                        <h6 class="alert-heading">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                            </svg>
                            Important Information
                        </h6>
                        <ul class="mb-0">
                            <li>All withdrawals require admin approval and are processed manually</li>
                            @if($withdrawalFeeSettings['fee_enabled'])
                                <li><strong>Processing fees are deducted immediately upon submission and are non-refundable</strong></li>
                                <li>If your withdrawal is rejected, only the withdrawal amount will be returned to your wallet</li>
                            @endif
                            <li>Please verify your payment method details are correct to avoid delays</li>
                            <li>Processing typically takes 1-3 business days depending on your chosen method</li>
                            <li>Ensure your Gcash/Maya accounts are active and can receive funds</li>
                        </ul>
                    </div>

                    <!-- Terms Agreement -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                        <label class="form-check-label" for="agree_terms">
                            <strong>I confirm and authorize this withdrawal</strong>
                        </label>
                        <div class="form-text">
                            I confirm that my payment method details are correct and authorize this withdrawal from my e-wallet.
                            @if($withdrawalFeeSettings['fee_enabled'])
                            I understand that processing fees will be deducted immediately upon submission and are non-refundable.
                            @endif
                            I understand that this transaction cannot be reversed once processed and requires admin approval.
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" id="submit-withdrawal-btn" class="btn btn-danger btn-lg flex-md-fill" disabled
                                {{ !$wallet->is_active || $wallet->balance <= 0 ? 'data-wallet-disabled="true"' : '' }}>
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-minus') }}"></use>
                            </svg>
                            Submit Withdrawal Request
                        </button>
                        <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-secondary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                            </svg>
                            View Transactions
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Withdrawal fee settings
    const withdrawalFeeSettings = {
        fee_enabled: {{ $withdrawalFeeSettings['fee_enabled'] ? 'true' : 'false' }},
        fee_type: '{{ $withdrawalFeeSettings['fee_type'] }}',
        fee_value: {{ $withdrawalFeeSettings['fee_value'] }},
        minimum_fee: {{ $withdrawalFeeSettings['minimum_fee'] }},
        maximum_fee: {{ $withdrawalFeeSettings['maximum_fee'] }}
    };

    function setAmount(amount) {
        document.getElementById('amount').value = amount;
        calculateFees(); // Update fees when quick amount is selected
    }

    function calculateFees() {
        const amountInput = document.getElementById('amount');
        const amount = parseFloat(amountInput.value) || 0;

        if (!withdrawalFeeSettings.fee_enabled || amount <= 0) {
            document.getElementById('fee-breakdown').classList.add('d-none');
            document.getElementById('fee-prompt').classList.remove('d-none');
            return;
        }

        // Calculate fee
        let fee = 0;
        if (withdrawalFeeSettings.fee_type === 'percentage') {
            fee = (amount * withdrawalFeeSettings.fee_value) / 100;
        } else {
            fee = withdrawalFeeSettings.fee_value;
        }

        // Apply min/max limits
        fee = Math.max(fee, withdrawalFeeSettings.minimum_fee);
        fee = Math.min(fee, withdrawalFeeSettings.maximum_fee);
        fee = Math.round(fee * 100) / 100; // Round to 2 decimal places

        const totalAmount = amount + fee;

        // Update display
        document.getElementById('withdrawal-amount-display').textContent = '$' + amount.toFixed(2);
        document.getElementById('fee-amount-display').textContent = '$' + fee.toFixed(2);
        document.getElementById('total-amount-display').textContent = '$' + totalAmount.toFixed(2);

        // Show breakdown
        document.getElementById('fee-breakdown').classList.remove('d-none');
        document.getElementById('fee-prompt').classList.add('d-none');
    }

    function togglePaymentDetails() {
        const paymentMethod = document.getElementById('payment_method').value;
        const customPaymentMethod = document.getElementById('custom_payment_method');
        const gcashDetails = document.getElementById('gcash_details');
        const mayaDetails = document.getElementById('maya_details');
        const cashDetails = document.getElementById('cash_details');
        const otherDetails = document.getElementById('other_details');

        // Hide all detail sections first
        gcashDetails.classList.add('d-none');
        mayaDetails.classList.add('d-none');
        cashDetails.classList.add('d-none');
        otherDetails.classList.add('d-none');
        customPaymentMethod.classList.add('d-none');

        // Clear required attributes
        document.getElementById('gcash_number').removeAttribute('required');
        document.getElementById('maya_number').removeAttribute('required');
        document.getElementById('pickup_location').removeAttribute('required');
        document.getElementById('payment_details').removeAttribute('required');
        customPaymentMethod.removeAttribute('required');

        // Show relevant section based on selection
        if (paymentMethod === 'Gcash') {
            gcashDetails.classList.remove('d-none');
            document.getElementById('gcash_number').setAttribute('required', 'required');
        } else if (paymentMethod === 'Maya') {
            mayaDetails.classList.remove('d-none');
            document.getElementById('maya_number').setAttribute('required', 'required');
        } else if (paymentMethod === 'Cash') {
            cashDetails.classList.remove('d-none');
            document.getElementById('pickup_location').setAttribute('required', 'required');
        } else if (paymentMethod === 'Others') {
            customPaymentMethod.classList.remove('d-none');
            otherDetails.classList.remove('d-none');
            customPaymentMethod.setAttribute('required', 'required');
            document.getElementById('payment_details').setAttribute('required', 'required');
        }
    }

    // Add event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethodSelect = document.getElementById('payment_method');

        paymentMethodSelect.addEventListener('change', togglePaymentDetails);

        // Add amount input listener for fee calculation
        const amountInput = document.getElementById('amount');
        amountInput.addEventListener('input', calculateFees);
        amountInput.addEventListener('change', calculateFees);

        // Handle terms agreement checkbox
        const agreeTermsCheckbox = document.getElementById('agree_terms');
        const submitButton = document.getElementById('submit-withdrawal-btn');

        function updateSubmitButton() {
            const isWalletDisabled = submitButton.hasAttribute('data-wallet-disabled');
            const isTermsChecked = agreeTermsCheckbox.checked;

            if (isWalletDisabled) {
                // If wallet is disabled, keep button disabled regardless of checkbox
                submitButton.disabled = true;
            } else {
                // Enable button only if terms are checked
                submitButton.disabled = !isTermsChecked;
            }
        }

        agreeTermsCheckbox.addEventListener('change', updateSubmitButton);

        // Initialize on page load if there's an old value
        if (paymentMethodSelect.value) {
            togglePaymentDetails();
        }

        // Calculate fees on initial load if amount is already set
        calculateFees();

        // Initialize submit button state
        updateSubmitButton();

        // Form validation
        const form = document.getElementById('withdraw-form');
        form.addEventListener('submit', function(e) {
            console.log('Form submission started');
            const paymentMethodSelect = document.getElementById('payment_method').value;
            const customPaymentMethod = document.getElementById('custom_payment_method').value;
            const paymentMethod = paymentMethodSelect || customPaymentMethod;

            console.log('Payment method select:', paymentMethodSelect);
            console.log('Custom payment method:', customPaymentMethod);
            console.log('Final payment method:', paymentMethod);

            if (!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method.');
                return false;
            }

            console.log('Form validation passed, submitting...');
            return true;
        });
    });
</script>
@endpush

@endsection