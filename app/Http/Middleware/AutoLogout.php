<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AutoLogout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = Session::get('last_activity');
            $timeout = config('session.lifetime') * 60; // Convert minutes to seconds
            
            // If there's no last activity recorded, set it now
            if (!$lastActivity) {
                Session::put('last_activity', time());
            } else {
                // Check if session has timed out
                if ((time() - $lastActivity) > $timeout) {
                    Auth::logout();
                    Session::flush();
                    Session::regenerate();
                    
                    // If it's an AJAX request, return JSON response
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'message' => 'Session expired due to inactivity.',
                            'redirect' => route('login')
                        ], 401);
                    }
                    
                    return redirect()->route('login')
                        ->with('message', 'You have been logged out due to inactivity.');
                }
            }
            
            // Update last activity timestamp
            Session::put('last_activity', time());
        }
        
        return $next($request);
    }
}
