<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * This middleware redirects authenticated users away from guest pages
     * (like login, register) to their appropriate dashboard.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$guards
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                // Get role name for more reliable checking
                $roleName = $user->role ? $user->role->role_name : null;
                
                // Redirect to appropriate dashboard based on role name
                switch ($roleName) {
                    case 'Admin':
                        return redirect()->route('admin.dashboard');
                    case 'Nutritionist':
                        return redirect()->route('nutritionist.dashboard');
                    case 'Parent':
                        return redirect()->route('parent.dashboard');
                    case 'Health Worker':
                    case 'Barangay Health Worker':
                        return redirect()->route('admin.dashboard'); // Or their specific dashboard
                    default:
                        // Fallback to role_id based checking
                        switch ($user->role_id) {
                            case 1:
                                return redirect()->route('admin.dashboard');
                            case 2:
                                return redirect()->route('nutritionist.dashboard');
                            case 5:
                                return redirect()->route('parent.dashboard');
                            default:
                                return redirect('/home');
                        }
                }
            }
        }

        return $next($request);
    }
}
