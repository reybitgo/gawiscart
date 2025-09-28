<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tax rate setting for e-commerce
        SystemSetting::set(
            'tax_rate',
            0.07,
            'decimal',
            'Tax rate for e-commerce purchases (as decimal, e.g., 0.07 for 7%)'
        );

        // Email verification setting (if not already exists)
        if (!SystemSetting::where('key', 'email_verification_required')->exists()) {
            SystemSetting::set(
                'email_verification_required',
                true,
                'boolean',
                'Whether email verification is required for new user registrations'
            );
        }
    }
}