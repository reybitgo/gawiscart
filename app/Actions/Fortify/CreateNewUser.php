<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Laravel\Fortify\Rules\Password;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Get the validation rules for passwords.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', 'min:8', 'confirmed'];
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'fullname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'terms' => ['required', 'accepted'],
        ], [
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
        ])->validate();

        $userData = [
            'fullname' => $input['fullname'],
            'username' => strtolower($input['username']),
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ];

        // If email verification is disabled, mark email as verified
        $emailVerificationRequired = SystemSetting::get('email_verification_required', true);
        if (!$emailVerificationRequired) {
            $userData['email_verified_at'] = now();
        }

        $user = User::create($userData);

        // Assign default member role
        $user->assignRole('member');

        return $user;
    }
}
