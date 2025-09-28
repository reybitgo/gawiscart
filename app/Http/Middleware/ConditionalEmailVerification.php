<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConditionalEmailVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if email verification after registration is required
        $registrationVerificationRequired = SystemSetting::get('email_verification_required', true);

        // Check if ongoing email verification is enabled (from system settings)
        $ongoingVerificationEnabled = SystemSetting::get('email_verification_enabled', true);

        // If both verification settings are disabled, skip verification entirely
        if (!$registrationVerificationRequired && !$ongoingVerificationEnabled) {
            return $next($request);
        }

        // If either verification setting is enabled, use Laravel's default verification middleware
        $emailVerificationMiddleware = new EnsureEmailIsVerified();
        return $emailVerificationMiddleware->handle($request, $next);
    }
}
