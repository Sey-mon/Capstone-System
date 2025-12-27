<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure user account is verified through either:
 * 1. Email verification (email_verified_at is not null), OR
 * 2. Admin activation (is_active is true)
 * 
 * This replaces Laravel's default 'verified' middleware to support dual verification paths.
 */
class EnsureAccountIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // If user is not authenticated, let auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Check verification status
        $isVerifiedByEmail = $user->email_verified_at !== null;
        $isActivatedByAdmin = $user->is_active === true;
        
        // Get user role
        $roleName = optional($user->role)->role_name;
        
        // Verification logic based on role:
        // - Parents: MUST verify email (self-registration requires email verification)
        // - Staff (Nutritionist, Admin, etc.): Can be activated by admin OR verify email
        if ($roleName === 'Parent') {
            // Parents must verify email - admin activation also sets email_verified_at
            if (!$isVerifiedByEmail) {
                return $this->denyAccess($request);
            }
        } else {
            // Staff accounts: require EITHER email verification OR admin activation
            if (!$isVerifiedByEmail && !$isActivatedByAdmin) {
                return $this->denyAccess($request);
            }
        }

        return $next($request);
    }

    /**
     * Deny access and redirect to verification gate
     */
    private function denyAccess(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your account must be verified before accessing this resource. Please verify your email.',
                'verified' => false
            ], 403);
        }

        return redirect()->route('verification.gate')
            ->with('message', 'Please verify your email to continue.');
    }
}
