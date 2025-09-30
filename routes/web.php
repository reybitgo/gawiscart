<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminPackageController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Member\WalletController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DatabaseResetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/test-login', function () {
    return view('test-login');
});

Route::middleware(['auth', 'enforce.2fa'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Package Routes (Public)
    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show');

    // Cart Routes
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add/{packageId}', [CartController::class, 'add'])->name('add');
        Route::patch('/update/{packageId}', [CartController::class, 'update'])->name('update');
        Route::delete('/remove/{packageId}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
        Route::get('/count', [CartController::class, 'getCount'])->name('count');
        Route::get('/summary', [CartController::class, 'getSummary'])->name('summary');
    });

    // Checkout Routes
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
        Route::get('/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('confirmation');
        Route::get('/order/{order}', [CheckoutController::class, 'orderDetails'])->name('order-details');
        Route::post('/order/{order}/cancel', [CheckoutController::class, 'cancelOrder'])->name('cancel-order');
        Route::get('/summary', [CheckoutController::class, 'getSummary'])->name('summary');
    });

    // Order History Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderHistoryController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderHistoryController::class, 'show'])->name('show');
        Route::post('/{order}/cancel', [OrderHistoryController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/reorder', [OrderHistoryController::class, 'reorder'])->name('reorder');
        Route::get('/{order}/invoice', [OrderHistoryController::class, 'invoice'])->name('invoice');
        Route::get('/ajax/list', [OrderHistoryController::class, 'ajax'])->name('ajax');
    });
});

// Admin Routes
Route::middleware(['auth', 'conditional.verified', 'enforce.2fa', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/wallet-management', [AdminController::class, 'walletManagement'])
        ->middleware('ewallet.security:wallet_management')
        ->name('wallet.management');
    Route::get('/transaction-approval', [AdminController::class, 'transactionApproval'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transaction.approval');
    Route::get('/system-settings', [AdminController::class, 'systemSettings'])
        ->middleware('ewallet.security:system_settings')
        ->name('system.settings');
    Route::get('/users', [AdminController::class, 'users'])->name('users');

    // Transaction Approval Routes
    Route::post('/transactions/{id}/approve', [AdminController::class, 'approveTransaction'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transactions.approve');
    Route::post('/transactions/{id}/reject', [AdminController::class, 'rejectTransaction'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transactions.reject');
    Route::post('/transactions/{id}/block', [AdminController::class, 'blockTransaction'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transactions.block');
    Route::post('/transactions/bulk-approval', [AdminController::class, 'bulkApproval'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transactions.bulk');
    Route::post('/transactions/export-report', [AdminController::class, 'exportTransactionReport'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transactions.export');
    Route::get('/transaction-stats', [AdminController::class, 'getTransactionStats'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transaction.stats');
    Route::get('/transactions/{id}/details', [AdminController::class, 'getTransactionDetails'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transactions.details');

    // Logs Routes
    Route::get('/logs', [AdminController::class, 'viewLogs'])
        ->middleware('ewallet.security:system_settings')
        ->name('logs');
    Route::post('/logs/export', [AdminController::class, 'exportLogs'])
        ->middleware('ewallet.security:system_settings')
        ->name('logs.export');
    Route::post('/logs/clear', [AdminController::class, 'clearOldLogs'])
        ->middleware('ewallet.security:system_settings')
        ->name('logs.clear');

    // Reports Routes
    Route::get('/reports', [AdminController::class, 'reports'])
        ->middleware('ewallet.security:system_settings')
        ->name('reports');
    Route::post('/reports/generate', [AdminController::class, 'generateReport'])
        ->middleware('ewallet.security:system_settings')
        ->name('reports.generate');
    Route::get('/reports/download/{reportId}', [AdminController::class, 'downloadReport'])
        ->middleware('ewallet.security:system_settings')
        ->name('reports.download');

    // System Settings Update Route
    Route::post('/system-settings', [AdminController::class, 'updateSystemSettings'])
        ->middleware('ewallet.security:system_settings')
        ->name('system.settings.update');
    Route::post('/system-settings/test-notification', [AdminController::class, 'testNotification'])
        ->middleware('ewallet.security:system_settings')
        ->name('system.settings.test-notification');

    // Admin Package Management Routes
    Route::resource('packages', AdminPackageController::class);
    Route::post('/packages/{package}/toggle-status', [AdminPackageController::class, 'toggleStatus'])->name('packages.toggle-status');

    // Admin Settings Routes
    Route::get('/application-settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('/application-settings', [AdminSettingsController::class, 'update'])->name('settings.update');

    // Admin Order Management Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
        Route::post('/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('update-status');
        Route::post('/{order}/add-notes', [AdminOrderController::class, 'addNotes'])->name('add-notes');
        Route::post('/{order}/update-tracking', [AdminOrderController::class, 'updateTracking'])->name('update-tracking');
        Route::post('/{order}/update-pickup', [AdminOrderController::class, 'updatePickup'])->name('update-pickup');
        Route::post('/bulk-update-status', [AdminOrderController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
        Route::get('/export', [AdminOrderController::class, 'export'])->name('export');
        Route::get('/analytics', [AdminOrderController::class, 'analytics'])->name('analytics');
        Route::get('/updates', [AdminOrderController::class, 'getUpdates'])->name('updates');
    });
});

// Database Reset Routes (Admin Only)
Route::middleware(['auth', 'conditional.verified', 'enforce.2fa', 'role:admin'])->group(function () {
    Route::get('/reset', [DatabaseResetController::class, 'reset'])->name('database.reset');
    Route::get('/reset-status', [DatabaseResetController::class, 'status'])->name('database.reset.status');
});

// Member/User Wallet Routes
Route::middleware(['auth', 'conditional.verified', 'enforce.2fa'])->prefix('wallet')->name('wallet.')->group(function () {
    Route::get('/deposit', [WalletController::class, 'deposit'])
        ->middleware('ewallet.security:deposit_funds')
        ->name('deposit');
    Route::post('/deposit', [WalletController::class, 'processDeposit'])
        ->middleware('ewallet.security:deposit_funds')
        ->name('deposit.process');

    Route::get('/transfer', [WalletController::class, 'transfer'])
        ->middleware('ewallet.security:transfer_funds')
        ->name('transfer');
    Route::post('/transfer', [WalletController::class, 'processTransfer'])
        ->middleware('ewallet.security:transfer_funds')
        ->name('transfer.process');

    Route::get('/withdraw', [WalletController::class, 'withdraw'])
        ->middleware('ewallet.security:withdraw_funds')
        ->name('withdraw');
    Route::post('/withdraw', [WalletController::class, 'processWithdraw'])
        ->middleware('ewallet.security:withdraw_funds')
        ->name('withdraw.process');

    Route::get('/transactions', [WalletController::class, 'transactions'])
        ->middleware('ewallet.security:view_transactions')
        ->name('transactions');
});

Route::middleware(['guest'])->group(function () {
    Route::redirect('/home', '/dashboard');
});

// Debug route for session configuration - Remove in production
Route::middleware(['auth'])->get('/debug/session-config', function () {
    return response()->json([
        'session_lifetime' => config('session.lifetime'),
        'session_expire_on_close' => config('session.expire_on_close'),
        'session_driver' => config('session.driver'),
        'database_session_timeout_enabled' => \App\Models\SystemSetting::get('session_timeout', false),
        'database_session_timeout_minutes' => \App\Models\SystemSetting::get('session_timeout_minutes', 15),
    ]);
});

// Temporary routes for testing error pages - Remove in production
Route::prefix('test')->name('test.')->group(function () {
    Route::get('/404', function () {
        abort(404);
    })->name('404');

    Route::get('/500', function () {
        abort(500);
    })->name('500');

    Route::get('/419', function () {
        abort(419);
    })->name('419');

    Route::get('/403', function () {
        abort(403);
    })->name('403');

    Route::get('/429', function () {
        abort(429);
    })->name('429');

    Route::get('/errors', function () {
        return view('test-errors');
    })->name('errors');
});
