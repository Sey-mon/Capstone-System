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
            
            // For form submissions, redirect back with error
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'csrf_error' => 'Security token has expired. Please refresh the page and try again. If you were filling out a long form, your progress may be lost.'
                ]);
        });
    })->create();
