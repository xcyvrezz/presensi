<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule auto-mark alpha every day at 23:59 (end of day)
Schedule::command('attendance:auto-mark-alpha')->dailyAt('23:59');

// Schedule auto-mark forgot check-out every day at 20:00
Schedule::command('attendance:auto-mark-forgot-checkout')->dailyAt('20:00');
