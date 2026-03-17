<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Assessment;
use App\Observers\AssessmentObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Assessment Observer to auto-sync patient data from completed assessments
        Assessment::observe(AssessmentObserver::class);
        
        // Define custom rate limiter for password reset by email
        RateLimiter::for('password.reset.email', function (Request $request) {
            $email = $request->input('email') ?: $request->ip();
            return [
                Limit::perMinute(3)->by($email)->response(function () {
                    return back()->withErrors([
                        'email' => 'Too many emails sent, please try again later.'
                    ])->withInput();
                }),
                Limit::perHour(10)->by($email)->response(function () {
                    return back()->withErrors([
                        'email' => 'Too many emails sent, please try again later.'
                    ])->withInput();
                }),
            ];
        });

        // Limit expensive LLM generation endpoint to protect uptime/cost.
        RateLimiter::for('feeding.program.generate', function (Request $request) {
            $key = ($request->user()?->id ?? 'guest') . '|' . $request->ip();
            return [
                Limit::perMinute(6)->by($key),
                Limit::perHour(60)->by($key),
            ];
        });

        // Register custom @assetv directive for smart cache busting
        // Usage: @assetv('css/login.css')
        Blade::directive('assetv', function ($path) {
            return "<?php \$p = {$path}; echo asset(\$p) . '?v=' . (file_exists(public_path(\$p)) ? filemtime(public_path(\$p)) : '0'); ?>";
        });
    }
}
