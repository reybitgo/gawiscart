<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    /**
     * Show the user's profile form.
     */
    public function show(Request $request)
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Check if this is a delivery address update (has delivery-specific fields)
        if ($request->has('delivery_time_preference') || $request->has('address')) {
            return $this->updateDeliveryAddress($request);
        }

        // Profile information update
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) use ($user) {
                    // Only check uniqueness if email is not null
                    if ($value !== null) {
                        $exists = \App\Models\User::where('email', $value)
                            ->where('id', '!=', $user->id)
                            ->whereNotNull('email')
                            ->exists();
                        if ($exists) {
                            $fail('The email has already been taken.');
                        }
                    }
                },
            ],
        ]);

        $oldEmail = $user->email;
        $newEmail = $validated['email'] ?? null;

        // If email is being changed
        if ($oldEmail !== $newEmail) {
            // Mark new email as unverified if email is provided
            $user->email_verified_at = $newEmail ? null : null;

            // Update email immediately - no restrictions
            $user->fill($validated);
            $user->save();

            if ($newEmail) {
                // Automatically send verification email for new/changed email
                $user->sendEmailVerificationNotification();
                return redirect()->route('profile.show')->with('success', "Email address updated successfully. A verification email has been sent to {$newEmail}.");
            } else {
                return redirect()->route('profile.show')->with('success', 'Email address removed successfully.');
            }
        }

        // If only username is being updated
        $user->fill($validated);
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile information updated successfully.');
    }

    /**
     * Update the user's delivery address information.
     */
    public function updateDeliveryAddress(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'fullname' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:20'],
            'delivery_instructions' => ['nullable', 'string', 'max:1000'],
            'delivery_time_preference' => ['nullable', 'in:anytime,morning,afternoon,weekend'],
        ]);

        $user->fill($validated);
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Delivery address updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.show')->with('success', 'Password updated successfully.');
    }
}
