<?php

use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run AQI fetch every 12 hours
// Schedule::command('app:fetch')->everySixHours();
// // Schedule::command('app:email-message')->dailyAt('9:00')->withoutOverlapping();
// Schedule::command('app:email-message')->everyMinute()->withoutOverlapping();
// Schedule::command('app:whatsapp-message')->everyMinute()->withoutOverlapping();


try {
    // get the single global settings row
    $settings = Settings::first();

    // Fallback defaults if settings missing
    $morning = $settings && $settings->morning_time
        ? Carbon::parse($settings->morning_time)->format('H:i')
        : '09:00';

    $evening = $settings && $settings->evening_time
        ? Carbon::parse($settings->evening_time)->format('H:i')
        : '18:00';

    // Schedule: both commands in morning
    Schedule::command('app:email-message')
        ->dailyAt($morning)
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/email_message.log'))
        ->runInBackground();

    Schedule::command('app:whatsapp-message')
        ->dailyAt($morning)
        ->withoutOverlapping()
             ->appendOutputTo(storage_path('logs/whatsapp_message.log'))
        ->runInBackground();

    // Schedule: both commands in evening
    Schedule::command('app:email-message')
        ->dailyAt($evening)
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/email_message.log'))
        ->runInBackground();

    Schedule::command('app:whatsapp-message')
        ->dailyAt($evening)
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/whatsapp_message.log'))
        ->runInBackground();

} catch (\Throwable $e) {
    // Log but don't crash scheduling
    Log::error('Error building schedule: ' . $e->getMessage());
}