<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use App\Mail\DepositNotification;
use App\Mail\WithdrawalNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WalletController extends Controller
{
    use AuthorizesRequests;

    public function deposit()
    {
        $this->authorize('deposit_funds');

        $paymentMethods = [
            'gcash_enabled' => \App\Models\SystemSetting::get('gcash_enabled', true),
            'gcash_number' => \App\Models\SystemSetting::get('gcash_number', ''),
            'gcash_name' => \App\Models\SystemSetting::get('gcash_name', ''),
            'maya_enabled' => \App\Models\SystemSetting::get('maya_enabled', true),
            'maya_number' => \App\Models\SystemSetting::get('maya_number', ''),
            'maya_name' => \App\Models\SystemSetting::get('maya_name', ''),
            'cash_enabled' => \App\Models\SystemSetting::get('cash_enabled', true),
            'others_enabled' => \App\Models\SystemSetting::get('others_enabled', true),
        ];

        return view('member.deposit', compact('paymentMethods'));
    }

    public function processDeposit(Request $request)
    {
        $this->authorize('deposit_funds');

        $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
            'payment_method' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        // Create transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => $request->amount,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'description' => 'Deposit via ' . $request->payment_method,
            'metadata' => [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        ]);

        // Send email notification to all admin users
        $adminUsers = User::role('admin')->get();
        foreach ($adminUsers as $admin) {
            Mail::to($admin->email)->send(new DepositNotification($transaction, $user));
        }

        session()->flash('success', 'Deposit request of $' . number_format($request->amount, 2) . ' has been submitted for approval. Reference: ' . $transaction->reference_number);

        return redirect()->route('wallet.transactions');
    }

    public function transfer()
    {
        $this->authorize('transfer_funds');

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        // Get transfer charge settings for JavaScript
        $transferSettings = [
            'charge_enabled' => \App\Models\SystemSetting::get('transfer_charge_enabled', false),
            'charge_type' => \App\Models\SystemSetting::get('transfer_charge_type', 'percentage'),
            'charge_value' => \App\Models\SystemSetting::get('transfer_charge_value', 0),
            'minimum_charge' => \App\Models\SystemSetting::get('transfer_minimum_charge', 0),
            'maximum_charge' => \App\Models\SystemSetting::get('transfer_maximum_charge', 999999),
        ];

        // Get frequent recipients (top 5 most frequently transferred to users)
        $frequentRecipients = \DB::table('transactions')
            ->join('users', function($join) {
                $join->on('users.id', '=', \DB::raw('CAST(JSON_UNQUOTE(JSON_EXTRACT(transactions.metadata, "$.recipient_id")) AS UNSIGNED)'));
            })
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', 'transfer_out')
            ->where('transactions.status', 'approved')
            ->select(
                'users.id',
                'users.username',
                'users.email',
                'users.fullname',
                \DB::raw('COUNT(*) as transfer_count'),
                \DB::raw('MAX(transactions.created_at) as last_transfer_at')
            )
            ->groupBy('users.id', 'users.username', 'users.email', 'users.fullname')
            ->orderBy('transfer_count', 'desc')
            ->orderBy('last_transfer_at', 'desc')
            ->limit(5)
            ->get();

        return view('member.transfer', compact('wallet', 'transferSettings', 'frequentRecipients'));
    }

    public function processTransfer(Request $request)
    {
        $this->authorize('transfer_funds');

        $request->validate([
            'recipient_identifier' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1|max:10000',
            'note' => 'nullable|string|max:255',
        ]);

        $sender = Auth::user();
        $senderWallet = $sender->getOrCreateWallet();

        // Find recipient by email or username
        $recipientIdentifier = $request->recipient_identifier;

        if (filter_var($recipientIdentifier, FILTER_VALIDATE_EMAIL)) {
            $recipient = \App\Models\User::where('email', $recipientIdentifier)->first();
        } else {
            $recipient = \App\Models\User::where('username', $recipientIdentifier)->first();
        }

        if (!$recipient) {
            return redirect()->back()->withErrors(['recipient_identifier' => 'Recipient not found. Please check the email or username.']);
        }

        // Check if trying to transfer to self
        if ($recipient->id === $sender->id) {
            return redirect()->back()->withErrors(['recipient_identifier' => 'You cannot transfer funds to yourself.']);
        }

        // Calculate transfer charge
        $transferAmount = $request->amount;
        $transferCharge = $this->calculateTransferCharge($transferAmount);
        $totalAmount = $transferAmount + $transferCharge;

        // Check if sender's wallet has sufficient balance (including charge)
        if ($senderWallet->balance < $totalAmount) {
            return redirect()->back()->withErrors(['amount' => 'Insufficient balance. You need $' . number_format($totalAmount, 2) . ' (Transfer: $' . number_format($transferAmount, 2) . ' + Fee: $' . number_format($transferCharge, 2) . '). Your current balance is $' . number_format($senderWallet->balance, 2)]);
        }

        // Check if sender's wallet is active
        if (!$senderWallet->is_active) {
            return redirect()->back()->withErrors(['general' => 'Your wallet is currently frozen. Please contact support.']);
        }

        try {
            \DB::transaction(function () use ($request, $sender, $senderWallet, $recipient, $transferAmount, $transferCharge, $totalAmount) {
                $recipientWallet = $recipient->getOrCreateWallet();

                // Create outgoing transaction for sender
                $outgoingTransaction = Transaction::create([
                    'user_id' => $sender->id,
                    'type' => 'transfer_out',
                    'amount' => $transferAmount,
                    'status' => 'approved', // Transfers are instant
                    'payment_method' => 'internal',
                    'description' => 'Transfer to ' . ($recipient->username ?: $recipient->email) . ($request->note ? ' - ' . $request->note : ''),
                    'metadata' => [
                        'recipient_id' => $recipient->id,
                        'recipient_email' => $recipient->email,
                        'recipient_username' => $recipient->username,
                        'transfer_charge' => $transferCharge,
                        'total_amount' => $totalAmount,
                        'note' => $request->note,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]
                ]);

                // Create incoming transaction for recipient
                $incomingTransaction = Transaction::create([
                    'user_id' => $recipient->id,
                    'type' => 'transfer_in',
                    'amount' => $transferAmount,
                    'status' => 'approved',
                    'payment_method' => 'internal',
                    'description' => 'Transfer from ' . ($sender->username ?: $sender->email) . ($request->note ? ' - ' . $request->note : ''),
                    'metadata' => [
                        'sender_id' => $sender->id,
                        'sender_email' => $sender->email,
                        'sender_username' => $sender->username,
                        'note' => $request->note,
                        'related_transaction_id' => $outgoingTransaction->id,
                    ]
                ]);

                // Create transfer charge transaction if applicable
                if ($transferCharge > 0) {
                    Transaction::create([
                        'user_id' => $sender->id,
                        'type' => 'transfer_charge',
                        'amount' => $transferCharge,
                        'status' => 'approved',
                        'payment_method' => 'internal',
                        'description' => 'Transfer fee for transaction to ' . ($recipient->username ?: $recipient->email),
                        'metadata' => [
                            'related_transaction_id' => $outgoingTransaction->id,
                            'transfer_amount' => $transferAmount,
                            'charge_type' => \App\Models\SystemSetting::get('transfer_charge_type', 'percentage'),
                            'charge_value' => \App\Models\SystemSetting::get('transfer_charge_value', 0),
                        ]
                    ]);
                }

                // Update reference numbers to link transactions
                $outgoingTransaction->update([
                    'metadata' => array_merge($outgoingTransaction->metadata ?? [], [
                        'related_transaction_id' => $incomingTransaction->id
                    ])
                ]);

                // Update wallet balances
                $senderWallet->decrement('balance', $totalAmount); // Deduct transfer amount + charge
                $senderWallet->update(['last_transaction_at' => now()]);

                $recipientWallet->increment('balance', $transferAmount); // Recipient gets only transfer amount
                $recipientWallet->update(['last_transaction_at' => now()]);
            });

            $message = 'Transfer of $' . number_format($transferAmount, 2) . ' to ' . ($recipient->username ?: $recipient->email) . ' completed successfully!';
            if ($transferCharge > 0) {
                $message .= ' (Transfer fee: $' . number_format($transferCharge, 2) . ' deducted)';
            }
            $message .= ' The funds have been transferred instantly.';
            session()->flash('success', $message);

            return redirect()->route('wallet.transactions');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['general' => 'Transfer failed. Please try again later.']);
        }
    }

    public function withdraw()
    {
        $this->authorize('withdraw_funds');

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        // Calculate pending withdrawals to show available balance (only pending withdrawal amounts, fees are already deducted)
        $pendingWithdrawals = Transaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->sum('amount');

        $availableBalance = $wallet->balance - $pendingWithdrawals;

        // Get payment method settings
        $paymentSettings = [
            'allow_others' => \App\Models\SystemSetting::get('others_enabled', true),
            'gcash_enabled' => \App\Models\SystemSetting::get('gcash_enabled', true),
            'maya_enabled' => \App\Models\SystemSetting::get('maya_enabled', true),
            'cash_enabled' => \App\Models\SystemSetting::get('cash_enabled', true),
        ];

        // Get withdrawal fee settings for JavaScript
        $withdrawalFeeSettings = [
            'fee_enabled' => \App\Models\SystemSetting::get('withdrawal_fee_enabled', false),
            'fee_type' => \App\Models\SystemSetting::get('withdrawal_fee_type', 'percentage'),
            'fee_value' => \App\Models\SystemSetting::get('withdrawal_fee_value', 0),
            'minimum_fee' => \App\Models\SystemSetting::get('withdrawal_minimum_fee', 0),
            'maximum_fee' => \App\Models\SystemSetting::get('withdrawal_maximum_fee', 999999),
        ];

        return view('member.withdraw', compact('wallet', 'paymentSettings', 'pendingWithdrawals', 'availableBalance', 'withdrawalFeeSettings'));
    }

    public function processWithdraw(Request $request)
    {
        $this->authorize('withdraw_funds');

        \Log::info('Withdrawal request started', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        $validationRules = [
            'amount' => 'required|numeric|min:1|max:10000',
            'payment_method' => 'required|string|max:255',
            'agree_terms' => 'required|accepted',
        ];

        // Determine the actual payment method (either from select or custom input)
        $paymentMethod = $request->payment_method;
        if ($paymentMethod === 'Others' && $request->custom_payment_method) {
            $paymentMethod = $request->custom_payment_method;
            $validationRules['custom_payment_method'] = 'required|string|max:255';
        }

        // Add conditional validation based on payment method
        if ($request->payment_method === 'Gcash') {
            $validationRules['gcash_number'] = 'required|string|max:11';
        } elseif ($request->payment_method === 'Maya') {
            $validationRules['maya_number'] = 'required|string|max:11';
        } elseif ($request->payment_method === 'Cash') {
            $validationRules['pickup_location'] = 'required|string|max:255';
        } elseif ($request->payment_method === 'Others') {
            $validationRules['payment_details'] = 'required|string|max:1000';
        }

        $request->validate($validationRules);

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        // Calculate withdrawal fee
        $withdrawalAmount = $request->amount;
        $withdrawalFee = $this->calculateWithdrawalFee($withdrawalAmount);
        $totalAmount = $withdrawalAmount + $withdrawalFee;

        // Calculate total pending withdrawal requests for this user (only withdrawal amounts, fees are already deducted)
        $pendingWithdrawals = Transaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->sum('amount');

        // Check if user's wallet has sufficient balance including pending withdrawals
        $availableBalance = $wallet->balance - $pendingWithdrawals;

        if ($availableBalance < $totalAmount) {
            $errorMessage = 'Insufficient available balance. ';
            if ($withdrawalFee > 0) {
                $errorMessage .= 'Total required: $' . number_format($totalAmount, 2) . ' (Withdrawal: $' . number_format($withdrawalAmount, 2) . ' + Fee: $' . number_format($withdrawalFee, 2) . '). ';
            } else {
                $errorMessage .= 'Total required: $' . number_format($withdrawalAmount, 2) . '. ';
            }
            if ($pendingWithdrawals > 0) {
                $errorMessage .= 'You have $' . number_format($pendingWithdrawals, 2) . ' in pending withdrawals. ';
            }
            $errorMessage .= 'Available balance: $' . number_format($availableBalance, 2) . '. Wallet balance: $' . number_format($wallet->balance, 2) . '.';

            return redirect()->back()->withErrors(['amount' => $errorMessage]);
        }

        // Check if user's wallet is active
        if (!$wallet->is_active) {
            return redirect()->back()->withErrors(['general' => 'Your wallet is currently frozen. Please contact support.']);
        }

        try {
            $transaction = null;

            \DB::transaction(function () use ($request, $user, $wallet, $withdrawalAmount, $withdrawalFee, $totalAmount, $paymentMethod, &$transaction) {
                // Build description based on payment method
                $description = 'Withdrawal via ' . $paymentMethod;
                if ($request->payment_method === 'Gcash') {
                    $description .= ' to ' . $request->gcash_number;
                } elseif ($request->payment_method === 'Maya') {
                    $description .= ' to ' . $request->maya_number;
                } elseif ($request->payment_method === 'Cash') {
                    $description .= ' - Pickup at: ' . $request->pickup_location;
                }

                // Build metadata based on payment method
                $metadata = [
                    'payment_method' => $paymentMethod,
                    'withdrawal_method' => strtolower($paymentMethod),
                    'withdrawal_fee' => $withdrawalFee,
                    'total_amount' => $totalAmount,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ];

                if ($request->payment_method === 'Gcash') {
                    $metadata['gcash_number'] = $request->gcash_number;
                } elseif ($request->payment_method === 'Maya') {
                    $metadata['maya_number'] = $request->maya_number;
                } elseif ($request->payment_method === 'Cash') {
                    $metadata['pickup_location'] = $request->pickup_location;
                } elseif ($request->payment_method === 'Others') {
                    $metadata['payment_details'] = $request->payment_details;
                }

                // Create withdrawal transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal',
                    'amount' => $withdrawalAmount,
                    'status' => 'pending', // Withdrawals need approval
                    'payment_method' => $paymentMethod,
                    'description' => $description,
                    'metadata' => $metadata,
                ]);

                // Create withdrawal fee transaction if applicable and deduct immediately
                if ($withdrawalFee > 0) {
                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'withdrawal_fee',
                        'amount' => $withdrawalFee,
                        'status' => 'approved', // Fee is deducted immediately upon submission
                        'payment_method' => 'internal',
                        'description' => 'Withdrawal processing fee for ' . $description,
                        'metadata' => [
                            'related_transaction_id' => $transaction->id,
                            'withdrawal_amount' => $withdrawalAmount,
                            'fee_type' => \App\Models\SystemSetting::get('withdrawal_fee_type', 'percentage'),
                            'fee_value' => \App\Models\SystemSetting::get('withdrawal_fee_value', 0),
                            'processed_at' => now(),
                            'auto_processed' => true,
                        ]
                    ]);

                    // Immediately deduct the fee from wallet balance
                    $wallet->decrement('balance', $withdrawalFee);
                }

                // For manual processing, don't deduct withdrawal amount until approved
                // Just update last transaction time
                $wallet->update(['last_transaction_at' => now()]);
            });

            // Send email notification to all admin users
            $adminUsers = User::role('admin')->get();
            foreach ($adminUsers as $admin) {
                try {
                    Mail::to($admin->email)->send(new WithdrawalNotification($transaction, $user));
                } catch (\Exception $emailError) {
                    \Log::warning('Failed to send withdrawal notification email', [
                        'admin_email' => $admin->email,
                        'error' => $emailError->getMessage()
                    ]);
                }
            }

            $message = 'Withdrawal request of $' . number_format($withdrawalAmount, 2) . ' via ' . $paymentMethod . ' has been submitted for approval.';
            if ($withdrawalFee > 0) {
                $message .= ' Processing fee of $' . number_format($withdrawalFee, 2) . ' has been deducted from your wallet immediately.';
            }
            $message .= ' You will receive your withdrawal funds once the admin approves your request.';

            session()->flash('success', $message);

            return redirect()->route('wallet.transactions');

        } catch (\Exception $e) {
            \Log::error('Withdrawal request failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['general' => 'Withdrawal request failed. Please try again later.']);
        }
    }

    /**
     * Calculate transfer charge based on system settings
     */
    private function calculateTransferCharge($amount)
    {
        if (!\App\Models\SystemSetting::get('transfer_charge_enabled', false)) {
            return 0;
        }

        $chargeType = \App\Models\SystemSetting::get('transfer_charge_type', 'percentage');
        $chargeValue = \App\Models\SystemSetting::get('transfer_charge_value', 0);
        $minCharge = \App\Models\SystemSetting::get('transfer_minimum_charge', 0);
        $maxCharge = \App\Models\SystemSetting::get('transfer_maximum_charge', 999999);

        if ($chargeType === 'percentage') {
            $charge = ($amount * $chargeValue) / 100;
        } else {
            $charge = $chargeValue;
        }

        // Apply min/max limits
        $charge = max($charge, $minCharge);
        $charge = min($charge, $maxCharge);

        return round($charge, 2);
    }

    /**
     * Calculate withdrawal fee based on system settings
     */
    private function calculateWithdrawalFee($amount)
    {
        if (!\App\Models\SystemSetting::get('withdrawal_fee_enabled', false)) {
            return 0;
        }

        $feeType = \App\Models\SystemSetting::get('withdrawal_fee_type', 'percentage');
        $feeValue = \App\Models\SystemSetting::get('withdrawal_fee_value', 0);
        $minFee = \App\Models\SystemSetting::get('withdrawal_minimum_fee', 0);
        $maxFee = \App\Models\SystemSetting::get('withdrawal_maximum_fee', 999999);

        if ($feeType === 'percentage') {
            $fee = ($amount * $feeValue) / 100;
        } else {
            $fee = $feeValue;
        }

        // Apply min/max limits
        $fee = max($fee, $minFee);
        $fee = min($fee, $maxFee);

        return round($fee, 2);
    }

    public function transactions(Request $request)
    {
        $this->authorize('view_transactions');

        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;

        $transactions = Auth::user()->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $wallet = Auth::user()->getOrCreateWallet();

        return view('member.transactions', compact('transactions', 'wallet'));
    }
}