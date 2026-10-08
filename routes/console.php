<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// The activity log keeps a year (ActivityLog::prunable). Runs when the
// server's cron calls `php artisan schedule:run` every minute.
Schedule::command('model:prune')->daily();
