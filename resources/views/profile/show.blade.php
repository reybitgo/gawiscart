@extends('layouts.admin')

@section('title', 'Profile')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <!-- Profile Information Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <svg class="icon icon-lg me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                </svg>
                <strong>Profile Information</strong>
            </div>
            <form method="PATCH" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                   id="username" name="username" value="{{ old('username', $user->username) }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="mt-2">
                                    <p class="text-sm text-warning">
                                        Your email address is unverified.
                                        <button form="send-verification" class="btn btn-link p-0 text-decoration-underline">
                                            Click here to re-send the verification email.
                                        </button>
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Created</label>
                            <input type="text" class="form-control" value="{{ $user->created_at->format('M d, Y') }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Type</label>
                            <input type="text" class="form-control" value="{{ ucfirst($user->getRoleNames()->first() ?? 'Member') }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Update Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <svg class="icon icon-lg me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                </svg>
                <strong>Update Password</strong>
            </div>
            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="showPasswords">
                                <label class="form-check-label" for="showPasswords">
                                    Show passwords
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                        </svg>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Wallet Information Card -->
        @if($user->wallet)
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <svg class="icon icon-lg me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                </svg>
                <strong>Wallet Information</strong>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h4 class="text-success">${{ number_format($user->wallet->balance, 2) }}</h4>
                    <p class="text-body-secondary">Current Balance</p>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border-end">
                            <div class="fs-6 fw-semibold">{{ $user->transactions()->where('status', 'completed')->count() }}</div>
                            <div class="text-uppercase text-body-secondary" style="font-size: 0.7rem;">Trxs</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end">
                            <div class="fs-6 fw-semibold">${{ number_format($user->transactions()->where('type', 'deposit')->where('status', 'completed')->sum('amount'), 0) }}</div>
                            <div class="text-uppercase text-body-secondary" style="font-size: 0.7rem;">In</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="fs-6 fw-semibold">${{ number_format($user->transactions()->where('type', 'withdraw')->where('status', 'completed')->sum('amount'), 0) }}</div>
                        <div class="text-uppercase text-body-secondary" style="font-size: 0.7rem;">Out</div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-primary btn-sm">View All Transactions</a>
            </div>
        </div>
        @endif

        <!-- Account Security Card -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <svg class="icon icon-lg me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                </svg>
                <strong>Account Security</strong>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <strong>Email Verification</strong>
                        <div class="text-body-secondary small">Secure your account</div>
                    </div>
                    <div>
                        @if($user->hasVerifiedEmail())
                            <span class="badge bg-success">Verified</span>
                        @else
                            <span class="badge bg-warning">Unverified</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Two-Factor Authentication</strong>
                        <div class="text-body-secondary small">Add extra security</div>
                    </div>
                    <div>
                        @if($user->two_factor_secret)
                            <span class="badge bg-success">Enabled</span>
                        @else
                            <span class="badge bg-secondary">Disabled</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email verification form -->
@if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const showPasswordsCheckbox = document.getElementById('showPasswords');
    const currentPasswordInput = document.getElementById('current_password');
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');

    // Function to toggle password visibility for all password fields
    function togglePasswordVisibility() {
        const inputType = showPasswordsCheckbox.checked ? 'text' : 'password';
        currentPasswordInput.type = inputType;
        passwordInput.type = inputType;
        passwordConfirmationInput.type = inputType;
    }

    // Listen for checkbox changes
    showPasswordsCheckbox.addEventListener('change', togglePasswordVisibility);
});
</script>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@endsection