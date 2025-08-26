<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the permanent deletion of old users to run weekly (every Sunday at 2 AM)
Schedule::command('users:permanent-delete')->weekly()->sundays()->at('02:00');
