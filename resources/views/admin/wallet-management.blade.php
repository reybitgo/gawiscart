@extends('layouts.admin')

@section('title', 'Wallet Management')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                    </svg>
                    Wallet Management
                </h4>
                <p class="text-body-secondary mb-0">Monitor and manage user e-wallets</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Wallet Overview Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-success-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">${{ number_format($totalBalance, 2) }}</div>
                    <div>Total Wallet Balance</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-primary-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $todayDeposits ?? 0 }}</div>
                    <div>Today's Deposits</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-danger-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">${{ number_format($todayWithdrawals ?? 0, 2) }}</div>
                    <div>Today's Withdrawals</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-minus') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-warning-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $pendingTransactions ?? 0 }}</div>
                    <div>Pending Transactions</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Additional Transaction Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card text-white bg-success-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $approvedTransactions ?? 0 }}</div>
                    <div>Approved Transactions</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card text-white bg-danger-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $rejectedTransactions ?? 0 }}</div>
                    <div>Rejected Transactions</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card text-white bg-info-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ ($pendingTransactions + $approvedTransactions + $rejectedTransactions) ?? 0 }}</div>
                    <div>Total Transactions</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- User Wallets Table -->
<div class="card mb-4">
    <div class="card-header">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-people') }}"></use>
        </svg>
        <strong>User Wallets</strong>
        <small class="text-body-secondary ms-auto">Overview of all member wallet balances and activity.</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Balance</th>
                        <th scope="col">Last Transaction</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wallets as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-3 {{ $user->wallet && $user->wallet->is_active ? 'bg-primary' : 'bg-danger' }}">
                                        <span class="text-white">{{ strtoupper(substr($user->fullname ?? $user->username, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $user->fullname ?? $user->username }}</div>
                                        <div class="text-body-secondary">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">${{ number_format($user->wallet ? $user->wallet->balance : 0, 2) }}</div>
                                @if($user->wallet && isset($user->wallet->last_transaction_at))
                                    <div class="text-body-secondary">Last activity: {{ $user->wallet->last_transaction_at->diffForHumans() }}</div>
                                @else
                                    <div class="text-body-secondary">No activity</div>
                                @endif
                            </td>
                            <td>
                                @if(isset($user->transactions) && $user->transactions->isNotEmpty())
                                    @php $lastTransaction = $user->transactions->first(); @endphp
                                    <div class="fw-semibold">
                                        {{ ucfirst($lastTransaction->type) }}:
                                        {{ $lastTransaction->type === 'deposit' ? '+' : '-' }}${{ number_format($lastTransaction->amount, 2) }}
                                    </div>
                                    <div class="text-body-secondary">{{ $lastTransaction->created_at->diffForHumans() }}</div>
                                @else
                                    <div class="text-body-secondary">No transactions</div>
                                @endif
                            </td>
                            <td>
                                @if($user->wallet)
                                    <span class="badge {{ $user->wallet->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->wallet->is_active ? 'Active' : 'Frozen' }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">No Wallet</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" title="View">
                                        <svg class="icon">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
                                        </svg>
                                    </button>
                                    @if($user->wallet)
                                        @if($user->wallet->is_active)
                                            <button class="btn btn-sm btn-outline-warning">Freeze</button>
                                        @else
                                            <button class="btn btn-sm btn-outline-success">Unfreeze</button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-body-secondary py-4">
                                No wallets found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($wallets) && $wallets->hasPages())
        <div class="card-footer">
            {{ $wallets->links('vendor.pagination.coreui') }}
        </div>
    @endif
</div>

<!-- Recent Transactions -->
<div class="card">
    <div class="card-header">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
        </svg>
        <strong>Recent Transactions</strong>
        <small class="text-body-secondary ms-auto">Latest wallet transactions across all users.</small>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($recentTransactions ?? [] as $transaction)
                <div class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 {{ $transaction->type === 'deposit' ? 'bg-success' : ($transaction->type === 'withdrawal' ? 'bg-danger' : 'bg-primary') }}">
                            <svg class="icon text-white">
                                @if($transaction->type === 'deposit')
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                                @elseif($transaction->type === 'withdrawal')
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-minus') }}"></use>
                                @else
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-horizontal') }}"></use>
                                @endif
                            </svg>
                        </div>
                        <div>
                            <div class="fw-semibold">
                                {{ ucfirst($transaction->type) }} by {{ $transaction->user->fullname ?? $transaction->user->username }}
                            </div>
                            <div class="text-body-secondary">
                                {{ ucfirst($transaction->payment_method ?? 'N/A') }} • {{ $transaction->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-semibold {{ $transaction->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                            {{ $transaction->type === 'deposit' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                        </div>
                        <span class="badge
                            @if($transaction->status == 'approved') bg-success
                            @elseif($transaction->status == 'rejected') bg-danger
                            @elseif($transaction->status == 'pending') bg-warning
                            @else bg-secondary @endif">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="list-group-item text-center text-body-secondary py-4">
                    No recent transactions
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- All Transactions Section -->
<div class="card mt-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                </svg>
                <strong>All Transactions</strong>
                <small class="text-body-secondary ms-2">Complete transaction history with filtering options</small>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('admin.wallet.management') }}" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">Status Filter</label>
                <select id="status" name="status" class="form-select">
                    <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending" {{ $statusFilter == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $statusFilter == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $statusFilter == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="type" class="form-label">Type Filter</label>
                <select id="type" name="type" class="form-select">
                    <option value="all" {{ $typeFilter == 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="deposit" {{ $typeFilter == 'deposit' ? 'selected' : '' }}>Deposits</option>
                    <option value="withdrawal" {{ $typeFilter == 'withdrawal' ? 'selected' : '' }}>Withdrawals</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-filter') }}"></use>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.wallet.management') }}" class="btn btn-secondary">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                        </svg>
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="card-body p-0">
        @if($allTransactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Transaction</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allTransactions as $transaction)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3 {{ $transaction->type === 'deposit' ? 'bg-success' : ($transaction->type === 'withdrawal' ? 'bg-danger' : 'bg-primary') }}">
                                            <svg class="icon text-white">
                                                @if($transaction->type === 'deposit')
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                                                @elseif($transaction->type === 'withdrawal')
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-minus') }}"></use>
                                                @else
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-horizontal') }}"></use>
                                                @endif
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ ucfirst($transaction->type) }} Request</div>
                                            <div class="text-body-secondary small">
                                                Ref: {{ $transaction->reference_number ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $transaction->user->fullname ?? $transaction->user->username }}</div>
                                    <div class="text-body-secondary">{{ $transaction->user->email }}</div>
                                    @if($transaction->user->wallet)
                                        <div class="text-body-secondary small">Balance: ${{ number_format($transaction->user->wallet->balance, 2) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold {{ $transaction->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->type === 'withdrawal' ? '-' : '+' }}${{ number_format($transaction->amount, 2) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ ucfirst($transaction->payment_method ?? 'N/A') }}</div>
                                </td>
                                <td>
                                    <span class="badge
                                        @if($transaction->status == 'approved') bg-success
                                        @elseif($transaction->status == 'rejected') bg-danger
                                        @elseif($transaction->status == 'pending') bg-warning
                                        @else bg-secondary @endif">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $transaction->created_at->format('M d, Y') }}</div>
                                    <div class="text-body-secondary">{{ $transaction->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="text-center">
                                    @if($transaction->status === 'pending')
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.transaction.approval') }}" class="btn btn-sm btn-outline-primary" title="Go to Transaction Approval">
                                                <svg class="icon">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-external-link') }}"></use>
                                                </svg>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $allTransactions->firstItem() ?? 0 }} to {{ $allTransactions->lastItem() ?? 0 }}
                        of {{ $allTransactions->total() }} transactions
                    </div>
                    {{ $allTransactions->links('vendor.pagination.coreui') }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <svg class="icon icon-3xl text-body-secondary mb-3">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-inbox') }}"></use>
                </svg>
                <h5 class="text-body-secondary">No transactions found</h5>
                <p class="text-body-secondary mb-0">
                    @if($statusFilter !== 'all' || $typeFilter !== 'all')
                        Try adjusting your filters to see more results.
                    @else
                        No transactions have been created yet.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<style>
/* Enhanced styling for wallet management page */
.table-responsive {
    border-radius: 8px;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.card {
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 12px 12px 0 0 !important;
}

.badge {
    font-size: 11px;
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 6px;
}

.btn-group .btn {
    border-radius: 6px !important;
    margin: 0 1px;
}

.form-select, .form-control {
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.form-select:focus, .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
}

.list-group-item {
    border: none;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1rem 1.25rem;
}

.list-group-item:last-child {
    border-bottom: none;
}

.list-group-item:hover {
    background-color: rgba(0, 123, 255, 0.02);
}

/* Pagination improvements */
.card-footer {
    background-color: #f8f9fa;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 0 0 12px 12px !important;
}

/* Transaction amount styling */
.text-success {
    color: #198754 !important;
}

.text-danger {
    color: #dc3545 !important;
}

/* Stats cards gradient enhancements */
.bg-primary-gradient {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.bg-success-gradient {
    background: linear-gradient(135deg, #198754 0%, #157347 100%);
}

.bg-danger-gradient {
    background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
}

.bg-warning-gradient {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
}

.bg-info-gradient {
    background: linear-gradient(135deg, #0dcaf0 0%, #0baccc 100%);
}

/* Action buttons styling */
.btn-outline-primary:hover, .btn-outline-success:hover, .btn-outline-warning:hover, .btn-outline-danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-sm {
    border-radius: 6px;
    font-weight: 500;
}

/* Filter section styling */
.card-body.border-bottom {
    background-color: #fafafa;
    border-radius: 0;
}

/* Empty state styling */
.text-center.py-5 {
    padding: 3rem 1rem !important;
}

/* Table responsive improvements */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 14px;
    }

    .btn-group .btn {
        font-size: 12px;
        padding: 0.25rem 0.5rem;
    }

    .avatar {
        width: 32px;
        height: 32px;
        font-size: 12px;
    }
}
</style>
@endsection