<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        $settings = [
            'tax_rate' => SystemSetting::get('tax_rate', 0.07),
            'email_verification_required' => SystemSetting::get('email_verification_required', true),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:1',
            'email_verification_required' => 'boolean',
        ]);

        // Update tax rate
        SystemSetting::set(
            'tax_rate',
            $request->tax_rate,
            'decimal',
            'Tax rate for e-commerce purchases (as decimal, e.g., 0.07 for 7%)'
        );

        // Update email verification setting
        SystemSetting::set(
            'email_verification_required',
            $request->boolean('email_verification_required'),
            'boolean',
            'Whether email verification is required for new user registrations'
        );

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully!');
    }
}