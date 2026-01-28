<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the permanent deletion of old users to run weekly (every Sunday at 2 AM)
Schedule::command('users:permanent-delete')->weekly()->sundays()->at('02:00');

// Schedule cleanup of expired password reset tokens daily at 2 AM
Schedule::command('password:cleanup')->daily()->at('02:00');

// Schedule deletion of accounts scheduled for deletion (runs daily at 3 AM)
Schedule::command('accounts:delete-scheduled')->daily()->at('03:00');

// Schedule automatic archiving of patients aged 5 years and above (runs monthly on the 1st at 1 AM)
Schedule::command('patients:archive-eligible --force')
    ->monthly()
    ->at('01:00')
    ->appendOutputTo(storage_path('logs/patient-archive.log'))
    ->emailOutputOnFailure(config('mail.admin_email', 'admin@example.com'));
