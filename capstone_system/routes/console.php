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
