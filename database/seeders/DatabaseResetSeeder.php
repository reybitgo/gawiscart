<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\SystemSetting;
use App\Models\Wallet;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MlmSetting;

class DatabaseResetSeeder extends Seeder
{
    /**
     * Run the database seeds to reset to initial state.
     * This seeder preserves current system settings and restores the first two users.
     */
    public function run(): void
    {
        $this->command->info('🔄 Starting database reset...');
        $this->command->newLine();

        // Step 0: Clear all caches and optimize
        $this->clearAllCaches();

        // Clear cache first
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Log Sprint 1 optimization status
        $this->logOptimizationStatus();

        // Step 1: Clear only user transactions and non-default users (preserve everything else)
        $this->clearUserData();

        // Step 2: Ensure roles and permissions exist (don't recreate if they exist)
        $this->ensureRolesAndPermissions();

        // Step 3: Re-create/ensure default users exist
        $this->ensureDefaultUsers();

        // Step 4: Ensure system settings are preserved (no action needed since we don't clear them)
        $this->ensureSystemSettings([]);

        // Step 4.5: Ensure application settings are preserved/created
        $this->ensureApplicationSettings();

        // Step 5: Create/update wallets for users
        $this->ensureUserWallets();

        // Step 6: Reset and reload preloaded packages
        $this->resetAndReloadPackages();

        // Step 7: Update reset tracking
        $this->updateResetTracking();

        // Step 8: Verify Phase 3 migration status
        $this->verifyPhase3Migration();

        $this->command->info('✅ Database reset completed successfully!');
        $this->command->info('👤 Admin: admin@gawisherbal.com / Admin123!@#');
        $this->command->info('👤 Member: member@gawisherbal.com / Member123!@#');
        $this->command->info('⚙️  System settings preserved');
        $this->command->info('⚙️  Application settings preserved');
        $this->command->info('📦 Preloaded packages restored with MLM settings');
        $this->command->info('🛒 Order history cleared (ready for new orders)');
        $this->command->info('↩️  Return requests cleared (ready for new returns)');
        $this->command->info('🔗 Referral clicks cleared (ready for new tracking)');
        $this->command->info('🔢 User IDs reset to sequential (1, 2)');
        $this->command->info('📍 Complete profile data for admin and member');
        $this->command->info('');
        $this->command->info('🚀 E-Commerce Platform Features:');
        $this->command->info('  ✅ 26-Status Order Lifecycle Management');
        $this->command->info('  ✅ Dual Delivery Methods (Office Pickup + Home Delivery)');
        $this->command->info('  ✅ Shopping Cart with Real-time Updates');
        $this->command->info('  ✅ Integrated E-Wallet Payment System');
        $this->command->info('  ✅ Complete Return & Refund System');
        $this->command->info('  ✅ Package Management with Inventory Tracking');
        $this->command->info('  ✅ Order Analytics Dashboard');
        $this->command->info('');
        $this->command->info('💰 MLM System Features (Phase 1, 2 & 3 Complete):');
        $this->command->info('  ✅ Phase 1: Core MLM Package & Registration');
        $this->command->info('    • 5-Level Commission Structure (L1: ₱200, L2-L5: ₱50 each)');
        $this->command->info('    • MLM Package Configuration (toggleable per package)');
        $this->command->info('    • Active/Inactive Level Toggling with Real-time Calculations');
        $this->command->info('    • MLM Settings Preservation (survives package toggle)');
        $this->command->info('    • Circular Reference Prevention (self-sponsorship & loops)');
        $this->command->info('    • Sponsor Relationship Validation');
        $this->command->info('    • Segregated Wallet Balances (MLM vs Purchase)');
        $this->command->info('    • Auto-generated Unique Referral Codes');
        $this->command->info('  ✅ Phase 2: Referral Link System & Auto-Fill Sponsor');
        $this->command->info('    • Shareable Referral Links with QR Codes');
        $this->command->info('    • Social Media Sharing (Facebook, WhatsApp, Messenger, Twitter)');
        $this->command->info('    • Referral Click Tracking (IP, User Agent, Timestamp)');
        $this->command->info('    • Auto-fill Sponsor on Registration');
        $this->command->info('    • Referral Statistics Dashboard (Clicks, Conversions, Rate)');
        $this->command->info('    • Copy to Clipboard Functionality');
        $this->command->info('    • Session-based Referral Code Storage');
        $this->command->info('    • Registration Conversion Tracking');
        $this->command->info('  ✅ Phase 3: Real-Time MLM Commission Distribution Engine');
        $this->command->info('    • Automatic Commission Distribution on Order Confirmation');
        $this->command->info('    • Upline Traversal (5 Levels: L1=₱200, L2-L5=₱50 each)');
        $this->command->info('    • Immediate Synchronous Processing (No Queue Required)');
        $this->command->info('    • Multi-Channel Notifications:');
        $this->command->info('      - Database notifications (always sent)');
        $this->command->info('      - Broadcast notifications (real-time if Echo configured)');
        $this->command->info('      - Email notifications (ONLY to verified emails)');
        $this->command->info('    • Transaction Audit Trail (level, source_order_id, metadata)');
        $this->command->info('    • MLM Balance Widget (Real-time Updates with Pulse Animation)');
        $this->command->info('    • Network Stats Panel (Direct Referrals, Total Earnings)');
        $this->command->info('    • Commission Processing Time: < 1 second per order');
        $this->command->info('    • Error Handling: Missing wallets, incomplete upline, duplicates');
        $this->command->info('    • Performance: 3 retry attempts with exponential backoff');
        $this->command->info('');
        $this->command->info('🔒 Performance & Security Enhancements:');
        $this->command->info('  ✅ Database indexes for faster queries');
        $this->command->info('  ✅ Eager loading to eliminate N+1 queries');
        $this->command->info('  ✅ Package caching for improved load times');
        $this->command->info('  ✅ Rate limiting on critical routes');
        $this->command->info('  ✅ CSRF protection on all AJAX operations');
        $this->command->info('  ✅ Transaction locking (prevents race conditions)');
        $this->command->info('  ✅ Secure cryptographic order number generation');
        $this->command->info('  ✅ Circular sponsor reference prevention (Model + Database)');
        $this->command->info('  ✅ MySQL triggers protect against raw SQL manipulation');
        $this->command->info('  ✅ User account suspension system (with auto-logout)');
        $this->command->info('  ✅ Session termination for suspended users');
        $this->command->info('');
        $this->command->info('📋 Return & Refund Process:');
        $this->command->info('  ✅ 7-day return window after delivery');
        $this->command->info('  ✅ Customer return request with proof images');
        $this->command->info('  ✅ Admin approval/rejection workflow');
        $this->command->info('  ✅ Automatic wallet refund processing');
    }

    /**
     * Clear user transactions and orders (preserve system settings, default users, roles, and permissions)
     */
    private function clearUserData(): void
    {
        $this->command->info('🗑️  Clearing user transactions and orders (preserving system settings, users, roles, and permissions)...');

        // Get default user IDs to preserve
        $defaultUserEmails = ['admin@gawisherbal.com', 'member@gawisherbal.com'];
        $defaultUserIds = DB::table('users')
            ->whereIn('email', $defaultUserEmails)
            ->pluck('id')
            ->toArray();

        // Disable foreign key checks for proper truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear referral clicks first (foreign key dependency on users)
        DB::table('referral_clicks')->truncate();
        $this->command->info('✅ Cleared all referral clicks');

        // Clear return requests (foreign key dependency on orders)
        DB::table('return_requests')->truncate();
        $this->command->info('✅ Cleared all return requests');

        // Clear order status histories (foreign key dependency on orders)
        DB::table('order_status_histories')->truncate();
        $this->command->info('✅ Cleared all order status histories');

        // Clear order items (foreign key dependency on orders)
        DB::table('order_items')->truncate();
        $this->command->info('✅ Cleared all order items');

        // Clear orders
        DB::table('orders')->truncate();
        $this->command->info('✅ Cleared all orders');

        // Clear transactions (all of them)
        DB::table('transactions')->truncate();
        $this->command->info('✅ Cleared all transactions');

        // Clear wallets except for default users
        if (!empty($defaultUserIds)) {
            DB::table('wallets')->whereNotIn('user_id', $defaultUserIds)->delete();
            $this->command->info('✅ Preserved wallets for ' . count($defaultUserIds) . ' default users');
        } else {
            DB::table('wallets')->truncate();
            $this->command->info('⚠️  No default users found to preserve wallets');
        }

        // Clear non-default users only (preserve all role and permission assignments)
        if (!empty($defaultUserIds)) {
            // Clear role assignments for non-default users only
            DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->whereNotIn('model_id', $defaultUserIds)
                ->delete();

            // Clear permission assignments for non-default users only
            DB::table('model_has_permissions')
                ->where('model_type', 'App\\Models\\User')
                ->whereNotIn('model_id', $defaultUserIds)
                ->delete();

            // Clear non-default users
            DB::table('users')->whereNotIn('id', $defaultUserIds)->delete();
            $this->command->info('✅ Preserved ' . count($defaultUserIds) . ' default users with their roles');
        } else {
            // If no default users exist, clear all users but preserve roles/permissions structure
            DB::table('model_has_roles')->truncate();
            DB::table('model_has_permissions')->truncate();
            DB::table('users')->truncate();
            $this->command->info('⚠️  No default users found to preserve');
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // NOTE: We deliberately preserve:
        // - system_settings table
        // - roles table
        // - permissions table
        // - role_has_permissions table (role-permission relationships)

        // Auto-increment counters are automatically reset by TRUNCATE
        $this->command->info('✅ Auto-increment counters reset for all cleared tables');
    }

    /**
     * Ensure roles and permissions exist (don't recreate if they exist)
     */
    private function ensureRolesAndPermissions(): void
    {
        $this->command->info('🔐 Ensuring roles and permissions exist...');

        // Check if roles and permissions already exist
        $existingRoles = Role::count();
        $existingPermissions = Permission::count();

        if ($existingRoles > 0 && $existingPermissions > 0) {
            $this->command->info("✅ Found $existingRoles roles and $existingPermissions permissions (preserved)");
            return;
        }

        // Only create if they don't exist
        $this->command->info('🔄 Creating missing roles and permissions...');

        // Create permissions for e-wallet operations
        $permissions = [
            // Admin-only permissions
            'wallet_management' => 'Manage user wallets and balances',
            'transaction_approval' => 'Approve or reject transactions',
            'system_settings' => 'Configure system settings',

            // Member permissions
            'deposit_funds' => 'Deposit funds to wallet',
            'transfer_funds' => 'Transfer funds to other users',
            'withdraw_funds' => 'Withdraw funds from wallet',
            'view_transactions' => 'View transaction history',
            'profile_update' => 'Update profile information',
        ];

        foreach ($permissions as $permission => $description) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['description' => $description]
            );
        }

        // Create admin role with all permissions
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Full system administrator access']
        );
        $adminRole->syncPermissions(Permission::all());

        // Create member role with limited permissions
        $memberRole = Role::firstOrCreate(
            ['name' => 'member'],
            ['description' => 'Regular user with wallet access']
        );
        $memberRole->syncPermissions([
            'deposit_funds',
            'transfer_funds',
            'withdraw_funds',
            'view_transactions',
            'profile_update'
        ]);

        $this->command->info("✅ Ensured " . count($permissions) . " permissions and 2 roles exist");
    }

    /**
     * Ensure default users exist and have correct roles with proper sequential IDs
     */
    private function ensureDefaultUsers(): void
    {
        $this->command->info('👥 Ensuring default users exist and have correct roles...');

        // Delete existing default users to recreate with proper IDs
        $defaultUserEmails = ['admin@gawisherbal.com', 'member@gawisherbal.com'];

        // Get existing user IDs before deletion
        $existingUsers = User::whereIn('email', $defaultUserEmails)->get();
        $existingWallets = [];

        foreach ($existingUsers as $user) {
            // Store wallet data if exists
            if ($user->wallet) {
                $existingWallets[$user->email] = [
                    'balance' => $user->wallet->balance,
                    'reserved_balance' => $user->wallet->reserved_balance,
                ];
            }
        }

        // Delete existing default users and their relationships
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($existingUsers as $user) {
            // Delete wallet
            DB::table('wallets')->where('user_id', $user->id)->delete();

            // Delete role assignments
            DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->where('model_id', $user->id)
                ->delete();

            // Delete permission assignments
            DB::table('model_has_permissions')
                ->where('model_type', 'App\\Models\\User')
                ->where('model_id', $user->id)
                ->delete();
        }

        // Delete the users
        User::whereIn('email', $defaultUserEmails)->delete();

        // Reset users auto-increment to 1
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create admin user (will get ID = 1) with complete profile - NO SPONSOR
        $admin = User::create([
            'username' => 'admin',
            'fullname' => 'System Administrator',
            'email' => 'admin@gawisherbal.com',
            'password' => Hash::make('Admin123!@#'),
            'email_verified_at' => now(),
            'suspended_at' => null,
            'phone' => '+63 (947) 367-7436',
            'address' => '123 Herbal Street',
            'address_2' => null,
            'city' => 'Wellness City',
            'state' => 'HC',
            'zip' => '12345',
            'delivery_instructions' => null,
            'delivery_time_preference' => 'anytime',
            'sponsor_id' => null, // Admin has no sponsor
            // referral_code will be auto-generated by User model boot method
        ]);

        $admin->syncRoles(['admin']);
        $this->command->info('✅ Created admin user (ID: ' . $admin->id . ', Referral: ' . $admin->referral_code . ')');

        // Create member user (will get ID = 2) sponsored by admin
        $member = User::create([
            'username' => 'member',
            'fullname' => 'John Michael Santos',
            'email' => 'member@gawisherbal.com',
            'password' => Hash::make('Member123!@#'),
            'email_verified_at' => now(),
            'suspended_at' => null,
            'phone' => '+63 (912) 456-7890',
            'address' => '456 Wellness Avenue',
            'address_2' => 'Unit 202',
            'city' => 'Health City',
            'state' => 'Metro Manila',
            'zip' => '54321',
            'delivery_instructions' => 'Ring doorbell twice. Gate code: 1234',
            'delivery_time_preference' => 'morning',
            'sponsor_id' => $admin->id, // Member is sponsored by admin
            // referral_code will be auto-generated by User model boot method
        ]);

        $member->syncRoles(['member']);
        $this->command->info('✅ Created member user (ID: ' . $member->id . ', Referral: ' . $member->referral_code . ', Sponsor: Admin)');

        $this->command->info('✅ Default users created with MLM relationships');
    }

    /**
     * Ensure system settings are preserved (they were not cleared, so just verify they exist)
     */
    private function ensureSystemSettings(array $currentSettings): void
    {
        $this->command->info('⚙️  Verifying system settings preservation...');

        $currentCount = SystemSetting::count();

        if ($currentCount > 0) {
            $this->command->info("✅ System settings preserved ($currentCount settings remain intact)");
            return;
        }

        // If somehow no settings exist (shouldn't happen), create minimal defaults
        $this->command->info('⚠️  No system settings found, creating minimal defaults...');
        $this->createMinimalDefaultSettings();
    }

    /**
     * Create minimal default settings if none exist
     */
    private function createMinimalDefaultSettings(): void
    {
        $this->command->info('⚙️  Creating minimal default settings...');

        $defaults = [
            ['key' => 'app_name', 'value' => 'Gawis iHerbal', 'type' => 'string', 'description' => 'Application name'],
            ['key' => 'app_version', 'value' => '1.0.0', 'type' => 'string', 'description' => 'Application version'],
            ['key' => 'email_verification_enabled', 'value' => true, 'type' => 'boolean', 'description' => 'Enable email verification'],
            ['key' => 'maintenance_mode', 'value' => false, 'type' => 'boolean', 'description' => 'Maintenance mode status']
        ];

        foreach ($defaults as $setting) {
            SystemSetting::create($setting);
        }

        $this->command->info("✅ Created " . count($defaults) . " default settings");
    }

    /**
     * Ensure application settings (tax rate, email verification after registration) are preserved/created
     */
    private function ensureApplicationSettings(): void
    {
        $this->command->info('⚙️  Verifying application settings preservation...');

        // Check if application settings exist in system_settings table
        $taxRateSetting = SystemSetting::where('key', 'tax_rate')->first();
        $emailVerifRegSetting = SystemSetting::where('key', 'email_verification_required')->first();

        if ($taxRateSetting && $emailVerifRegSetting) {
            $this->command->info("✅ Application settings preserved (tax rate, email verification)");
            return;
        }

        // Create default application settings if they don't exist
        $this->command->info('⚙️  Creating default application settings...');

        if (!$taxRateSetting) {
            SystemSetting::create([
                'key' => 'tax_rate',
                'value' => 0.12,
                'type' => 'decimal',
                'description' => 'E-commerce tax rate (0.0 to 1.0)'
            ]);
            $this->command->info('✅ Created default tax rate: 12%');
        }

        if (!$emailVerifRegSetting) {
            SystemSetting::create([
                'key' => 'email_verification_required',
                'value' => true,
                'type' => 'boolean',
                'description' => 'Require email verification after registration'
            ]);
            $this->command->info('✅ Created default email verification setting: enabled');
        }
    }

    /**
     * Reset wallets for default users to initial balances
     */
    private function ensureUserWallets(): void
    {
        $this->command->info('💰 Resetting default user wallets to initial balances...');

        $admin = User::where('email', 'admin@gawisherbal.com')->first();
        $member = User::where('email', 'member@gawisherbal.com')->first();

        if ($admin) {
            // Reset admin wallet with segregated balances (MLM system)
            Wallet::updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'balance' => 0.00, // Legacy balance (will be deprecated)
                    'mlm_balance' => 0.00, // MLM earnings (withdrawable)
                    'purchase_balance' => 1000.00, // Purchase credits
                    'reserved_balance' => 0.00,
                    'is_active' => true
                ]
            );
        }

        if ($member) {
            // Reset member wallet with segregated balances (MLM system)
            Wallet::updateOrCreate(
                ['user_id' => $member->id],
                [
                    'balance' => 0.00, // Legacy balance (will be deprecated)
                    'mlm_balance' => 0.00, // MLM earnings (withdrawable)
                    'purchase_balance' => 1000.00, // Purchase credits (₱1,000 for Starter Package)
                    'reserved_balance' => 0.00,
                    'is_active' => true
                ]
            );
        }

        $this->command->info('✅ Default user wallets reset with MLM segregated balances');
        $this->command->info('💰 Admin: ₱1,000 (Purchase Balance)');
        $this->command->info('💰 Member: ₱1,000 (Purchase Balance for Starter Package)');
    }

    /**
     * Reset and reload preloaded packages
     */
    private function resetAndReloadPackages(): void
    {
        $this->command->info('📦 Resetting and reloading preloaded packages...');

        // Clear all existing packages (force delete to completely remove)
        Package::withTrashed()->forceDelete();
        $this->command->info('🗑️  Cleared all existing packages');

        // Clear MLM settings (will be recreated with packages)
        DB::table('mlm_settings')->truncate();
        $this->command->info('🗑️  Cleared all MLM settings');

        // Clear package cache (Sprint 1 enhancement)
        $this->clearPackageCache();

        // Reset auto-increment counters
        DB::statement('ALTER TABLE packages AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE mlm_settings AUTO_INCREMENT = 1');

        // Reload preloaded packages by calling the PackageSeeder
        $this->command->info('🔄 Reloading preloaded packages with MLM settings...');
        $this->call(\Database\Seeders\PackageSeeder::class);

        $packageCount = Package::count();
        $mlmSettingsCount = MlmSetting::count();
        $this->command->info("✅ Reloaded {$packageCount} preloaded packages with {$mlmSettingsCount} MLM settings");
    }

    /**
     * Clear all package-related caches
     */
    private function clearPackageCache(): void
    {
        try {
            // Clear all package caches using pattern matching
            $packages = DB::table('packages')->pluck('id');

            foreach ($packages as $packageId) {
                Cache::forget("package_{$packageId}");
            }

            $this->command->info("🗑️  Cleared cache for " . count($packages) . " packages");
        } catch (\Exception $e) {
            $this->command->warn('⚠️  Failed to clear some package caches: ' . $e->getMessage());
        }
    }

    /**
     * Update reset tracking information
     */
    private function updateResetTracking(): void
    {
        $this->command->info('📊 Updating reset tracking...');

        // Update reset count
        $currentCount = SystemSetting::get('reset_count', 0);
        SystemSetting::set('reset_count', $currentCount + 1, 'integer', 'Number of times database has been reset');
        SystemSetting::set('last_reset_date', now()->toISOString(), 'string', 'Last database reset timestamp');

        $this->command->info('✅ Reset tracking updated');
    }

    /**
     * Log Sprint 1 optimization status
     */
    private function logOptimizationStatus(): void
    {
        $this->command->info('🔍 Checking Sprint 1 optimizations...');

        // Check for performance indexes migration
        $indexMigration = DB::table('migrations')
            ->where('migration', 'like', '%add_performance_indexes_to_tables%')
            ->first();

        if ($indexMigration) {
            $this->command->info('✅ Performance indexes migration detected');
        } else {
            $this->command->warn('⚠️  Performance indexes migration not found - will be applied');
        }

        // Check cache driver
        $cacheDriver = config('cache.default');
        $this->command->info("ℹ️  Cache driver: {$cacheDriver}");

        if ($cacheDriver === 'redis') {
            $this->command->info('✅ Redis cache configured (optimal)');
        } elseif ($cacheDriver === 'database') {
            $this->command->info('ℹ️  Database cache configured (consider Redis for production)');
        }
    }

    /**
     * Clear all application caches for fresh start
     */
    private function clearAllCaches(): void
    {
        $this->command->info('🧹 Clearing all caches...');

        try {
            // Clear application cache
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            $this->command->info('  ✅ Application cache cleared');

            // Clear config cache
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            $this->command->info('  ✅ Configuration cache cleared');

            // Clear route cache
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            $this->command->info('  ✅ Route cache cleared');

            // Clear view cache
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            $this->command->info('  ✅ View cache cleared');

            // Clear compiled classes
            \Illuminate\Support\Facades\Artisan::call('clear-compiled');
            $this->command->info('  ✅ Compiled classes cleared');

            $this->command->newLine();
        } catch (\Exception $e) {
            $this->command->warn('⚠️  Some caches could not be cleared: ' . $e->getMessage());
        }
    }

    /**
     * Verify Phase 3 MLM commission migration status
     */
    private function verifyPhase3Migration(): void
    {
        $this->command->newLine();
        $this->command->info('🔍 Verifying Phase 3: MLM Commission Distribution...');

        // Check for Phase 3 migration
        $phase3Migration = DB::table('migrations')
            ->where('migration', 'like', '%add_mlm_fields_to_transactions%')
            ->first();

        if ($phase3Migration) {
            $this->command->info('✅ Phase 3 migration applied: MLM fields added to transactions table');

            // Verify the actual columns exist
            try {
                $hasLevel = DB::getSchemaBuilder()->hasColumn('transactions', 'level');
                $hasSourceOrderId = DB::getSchemaBuilder()->hasColumn('transactions', 'source_order_id');
                $hasSourceType = DB::getSchemaBuilder()->hasColumn('transactions', 'source_type');

                if ($hasLevel && $hasSourceOrderId && $hasSourceType) {
                    $this->command->info('✅ Verified: All Phase 3 transaction columns present');
                    $this->command->info('  • level (MLM level tracking)');
                    $this->command->info('  • source_order_id (order linkage)');
                    $this->command->info('  • source_type (transaction categorization)');
                } else {
                    $this->command->warn('⚠️  Phase 3 migration exists but columns missing - run: php artisan migrate');
                }
            } catch (\Exception $e) {
                $this->command->warn('⚠️  Could not verify Phase 3 columns: ' . $e->getMessage());
            }

            // Phase 3 Information
            $this->command->newLine();
            $this->command->info('📌 Phase 3 MLM Commission System:');
            $this->command->info('  ✅  Commissions are processed IMMEDIATELY upon package purchase');
            $this->command->info('  ✅  No queue worker required - synchronous processing');
            $this->command->info('  ✅  Active users (who bought packages) earn from downline purchases');
            $this->command->newLine();
            $this->command->info('  ℹ️  Optional: Monitor application logs:');
            $this->command->info('     php artisan pail --timeout=0');
        } else {
            $this->command->warn('⚠️  Phase 3 migration NOT found');
            $this->command->warn('     Run: php artisan migrate');
            $this->command->warn('     Expected migration: *_add_mlm_fields_to_transactions_table.php');
        }

        $this->command->newLine();
    }
}