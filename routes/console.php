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
    $morningTime = $settings && $settings->morning_time
        ? Carbon::parse($settings->morning_time)
        : Carbon::parse('09:00');

    $eveningTime = $settings && $settings->evening_time
        ? Carbon::parse($settings->evening_time)
        : Carbon::parse('18:00');

    // Calculate times 2 hours before morning and evening
    $morningUpdateTime = $morningTime->copy()->subHours(2)->format('H:i');
    $eveningUpdateTime = $eveningTime->copy()->subHours(2)->format('H:i');

    // Schedule AQI fetch 2 hours before morning time
    Schedule::command('aqi:fetch-all')
        ->dailyAt($morningUpdateTime)
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/aqi_fetch.log'))
        ->runInBackground();

    // Schedule AQI fetch 2 hours before evening time
    Schedule::command('aqi:fetch-all')
        ->dailyAt($eveningUpdateTime)
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/aqi_fetch.log'))
        ->runInBackground();

    Log::info("📅 [Scheduler] AQI updates scheduled: Morning at {$morningUpdateTime} (2 hours before {$morningTime->format('H:i')}), Evening at {$eveningUpdateTime} (2 hours before {$eveningTime->format('H:i')})");

    // Schedule: both commands in morning
    Schedule::command('app:email-message')
        ->dailyAt($morningTime->format('H:i'))
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/email_message.log'))
        ->runInBackground();

    Schedule::command('app:whatsapp-message')
        ->dailyAt($morningTime->format('H:i'))
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/whatsapp_message.log'))
        ->runInBackground();

    // Schedule: both commands in evening
    Schedule::command('app:email-message')
        ->dailyAt($eveningTime->format('H:i'))
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/email_message.log'))
        ->runInBackground();

    Schedule::command('app:whatsapp-message')
        ->dailyAt($eveningTime->format('H:i'))
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/whatsapp_message.log'))
        ->runInBackground();

} catch (\Throwable $e) {
    // Log but don't crash scheduling
    Log::error('Error building schedule: ' . $e->getMessage());
}