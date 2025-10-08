<?php

namespace App\Console\Commands;

use App\Jobs\EmailJob;
use App\Jobs\SendWhatsappMessageJob;
use App\Models\AQI;
use App\Models\City;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WhatsappMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:whatsapp-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('📢 [Whatsapp Cron Start] AQI Message Scheduler triggered at ' . now()->toDateTimeString());

        try {
            $cities = City::select('name', 'aqi')->get();
            $message = AQI::where('type', 'whatsapp')->get();
    
            // dd($cities, $message);
            if ($cities->isEmpty()) {
                return back()->with('error', 'No records found to send WhatsApp messages.');
            }
    
            $count = 0;
            foreach ($cities as $row) {
                if ($row['aqi'] != 'Error') {
                    $to = "923045039326"; // Or $row['phone'] if exists
                    if ($row['aqi'] <= 50) {
                        $message = ($messages['good'] ?? "the air quality in {$row['name']} is Good 😊 (AQI: {$row['aqi']}). Enjoy your day!");
                    } elseif ($row['aqi'] <= 100) {
                        $message = ($messages['moderate'] ?? "the air quality in {$row['name']} is Moderate 🙂 (AQI: {$row['aqi']}). It’s generally okay.");
                    } elseif ($row['aqi'] <= 150) {
                        $message = ($messages['unhealthy_sensitive'] ?? "the air quality in {$row['name']} is Unhealthy for Sensitive Groups 😷 (AQI: {$row['aqi']}). Be careful if you have breathing issues.");
                    } elseif ($row['aqi'] <= 200) {
                        $message = ($messages['unhealthy'] ?? "the air quality in {$row['name']} is Unhealthy ❌ (AQI: {$row['aqi']}). Try to limit outdoor activity.");
                    } elseif ($row['aqi'] <= 300) {
                        $message = ($messages['very_unhealthy'] ?? "the air quality in {$row['name']} is Very Unhealthy ⚠️ (AQI: {$row['aqi']}). Consider staying indoors.");
                    } else {
                        $message = ($messages['hazardous'] ?? "the air quality in {$row['name']} is Hazardous ☠️ (AQI: {$row['aqi']}). Stay safe and avoid going outside.");
                    }
                    // dump( $row['name'], $row['aqi'], $message);
                    dispatch(new SendWhatsappMessageJob($to, $row['name'], $row['aqi'], $message));
                    $count++;
                }
            }

            Log::info("📨 Successfully queued {$count} messages for sending.");

        } catch (Exception $exception) {
            Log::error('💥 [Whatsapp Cron Error] AQI Message Scheduler failed: ' . $exception->getMessage());
        }

        Log::info('🏁 [Whatsapp Cron End] AQI Message Scheduler finished at ' . now()->toDateTimeString());
    }
}
