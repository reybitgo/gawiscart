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
        // Check if email verification is enabled in system settings
        $emailVerificationEnabled = SystemSetting::get('email_verification_enabled', true);

        // If email verification is disabled globally, skip verification
        if (!$emailVerificationEnabled) {
            return $next($request);
        }

        // If email verification is enabled, use Laravel's default verification middleware
        $emailVerificationMiddleware = new EnsureEmailIsVerified();
        return $emailVerificationMiddleware->handle($request, $next);
    }
}
