<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Assessment;
use App\Observers\AssessmentObserver;

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
    }
}
