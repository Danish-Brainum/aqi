<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run AQI fetch every 12 hours
Schedule::command('app:fetch')->everySixHours();
// Schedule::command('app:email-message')->dailyAt('9:00')->withoutOverlapping();
Schedule::command('app:email-message')->everyMinute()->withoutOverlapping();
Schedule::command('app:whatsapp-message')->everyMinute()->withoutOverlapping();
