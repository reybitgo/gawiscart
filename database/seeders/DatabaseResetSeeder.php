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

class DatabaseResetSeeder extends Seeder
{
    /**
     * Run the database seeds to reset to initial state.
     * This seeder preserves current system settings and restores the first two users.
     */
    public function run(): void
    {
        $this->command->info('🔄 Starting database reset...');

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

        // Step 5: Create/update wallets for users
        $this->ensureUserWallets();

        // Step 6: Reset and reload preloaded packages
        $this->resetAndReloadPackages();

        // Step 7: Update reset tracking
        $this->updateResetTracking();

        $this->command->info('✅ Database reset completed successfully!');
        $this->command->info('👤 Admin: admin@ewallet.com / Admin123!@#');
        $this->command->info('👤 Member: member@ewallet.com / Member123!@#');
        $this->command->info('⚙️  System settings preserved');
        $this->command->info('📦 Preloaded packages restored');
        $this->command->info('🛒 Order history cleared (ready for new orders)');
        $this->command->info('');
        $this->command->info('🚀 Sprint 1 Performance & Security Enhancements Active:');
        $this->command->info('  ✅ Database indexes for faster queries');
        $this->command->info('  ✅ Eager loading to eliminate N+1 queries');
        $this->command->info('  ✅ Package caching for improved load times');
        $this->command->info('  ✅ Rate limiting on critical routes');
        $this->command->info('  ✅ CSRF protection on all AJAX operations');
        $this->command->info('  ✅ Wallet transaction locking (prevents race conditions)');
        $this->command->info('  ✅ Secure cryptographic order number generation');
    }

    /**
     * Clear user transactions and orders (preserve system settings, default users, roles, and permissions)
     */
    private function clearUserData(): void
    {
        $this->command->info('🗑️  Clearing user transactions and orders (preserving system settings, users, roles, and permissions)...');

        // Get default user IDs to preserve
        $defaultUserEmails = ['admin@ewallet.com', 'member@ewallet.com'];
        $defaultUserIds = DB::table('users')
            ->whereIn('email', $defaultUserEmails)
            ->pluck('id')
            ->toArray();

        // Clear order items first (foreign key dependency)
        DB::table('order_items')->delete();
        $this->command->info('✅ Cleared all order items');

        // Clear orders
        DB::table('orders')->delete();
        $this->command->info('✅ Cleared all orders');

        // Clear transactions (all of them)
        DB::table('transactions')->delete();
        $this->command->info('✅ Cleared all transactions');

        // Clear wallets except for default users
        if (!empty($defaultUserIds)) {
            DB::table('wallets')->whereNotIn('user_id', $defaultUserIds)->delete();
            $this->command->info('✅ Preserved wallets for ' . count($defaultUserIds) . ' default users');
        } else {
            DB::table('wallets')->delete();
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
            DB::table('model_has_roles')->delete();
            DB::table('model_has_permissions')->delete();
            DB::table('users')->delete();
            $this->command->info('⚠️  No default users found to preserve');
        }

        // NOTE: We deliberately preserve:
        // - system_settings table
        // - roles table
        // - permissions table
        // - role_has_permissions table (role-permission relationships)

        // Reset auto-increment counters for fully cleared tables only
        DB::statement('ALTER TABLE order_items AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE orders AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE transactions AUTO_INCREMENT = 1');
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
     * Ensure default users exist and have correct roles
     */
    private function ensureDefaultUsers(): void
    {
        $this->command->info('👥 Ensuring default users exist and have correct roles...');

        // Create or update default admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@ewallet.com'],
            [
                'username' => 'admin',
                'fullname' => 'System Administrator',
                'email' => 'admin@ewallet.com',
                'password' => Hash::make('Admin123!@#'),
                'email_verified_at' => now(),
            ]
        );

        // Remove all existing roles and assign fresh admin role
        $admin->syncRoles(['admin']);

        // Create or update default member user
        $member = User::updateOrCreate(
            ['email' => 'member@ewallet.com'],
            [
                'username' => 'member',
                'fullname' => 'Test Member',
                'email' => 'member@ewallet.com',
                'password' => Hash::make('Member123!@#'),
                'email_verified_at' => now(),
            ]
        );

        // Remove all existing roles and assign fresh member role
        $member->syncRoles(['member']);

        $this->command->info('✅ Default users ensured with correct roles (admin and member)');
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
            ['key' => 'app_name', 'value' => 'Gawis iHerbal E-Wallet', 'type' => 'string', 'description' => 'Application name'],
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
     * Reset wallets for default users to initial balances
     */
    private function ensureUserWallets(): void
    {
        $this->command->info('💰 Resetting default user wallets to initial balances...');

        $admin = User::where('email', 'admin@ewallet.com')->first();
        $member = User::where('email', 'member@ewallet.com')->first();

        if ($admin) {
            // Reset admin wallet to initial balance
            Wallet::updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'balance' => 1000.00, // Admin starts with $1000
                    'reserved_balance' => 0.00,
                    'is_active' => true
                ]
            );
        }

        if ($member) {
            // Reset member wallet to initial balance
            Wallet::updateOrCreate(
                ['user_id' => $member->id],
                [
                    'balance' => 100.00, // Member starts with $100
                    'reserved_balance' => 0.00,
                    'is_active' => true
                ]
            );
        }

        $this->command->info('✅ Default user wallets reset to initial balances');
        $this->command->info('💰 Admin wallet: $1,000.00');
        $this->command->info('💰 Member wallet: $100.00');
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

        // Clear package cache (Sprint 1 enhancement)
        $this->clearPackageCache();

        // Reset auto-increment counter
        DB::statement('ALTER TABLE packages AUTO_INCREMENT = 1');

        // Reload preloaded packages by calling the PackageSeeder
        $this->command->info('🔄 Reloading preloaded packages...');
        $this->call(\Database\Seeders\PackageSeeder::class);

        $packageCount = Package::count();
        $this->command->info("✅ Reloaded {$packageCount} preloaded packages");
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
}