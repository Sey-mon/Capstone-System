<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'account.verified' => \App\Http\Middleware\EnsureAccountIsVerified::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'prevent.back' => \App\Http\Middleware\PreventBackHistory::class,
        ]);
        
        // Add auto logout middleware to web group
        $middleware->web(append: [
            \App\Http\Middleware\AutoLogout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle CSRF token mismatch errors
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $exception, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'CSRF token mismatch. Please refresh the page and try again.',
                    'code' => 'CSRF_TOKEN_MISMATCH'
                ], 419);
            }
            
            // For login pages, redirect to login with clear message
            if ($request->is('login') || $request->is('/') || $request->is('staff/login')) {
                return redirect()->route('login')
                    ->withErrors([
                        'csrf_error' => 'Your session has expired. Please login again.'
                    ])
                    ->with('warning', 'For security, please refresh the page if you used the back button.');
            }
            
            // For form submissions, redirect back with error
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'csrf_error' => 'Security token has expired. Please refresh the page and try again. If you were filling out a long form, your progress may be lost.'
                ]);
        });
    })->create();
