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
    
            if ($cities->isEmpty()) {
                Log::warning('No cities found to send WhatsApp messages.');
                return;
            }
    
            $count = 0;
            foreach ($cities as $row) {
                // Access as object property, not array
                $aqi = $row->aqi;
                $cityName = $row->name;
                
                // Check if AQI is valid (not null, not 'Error', and numeric)
                if ($aqi !== null && $aqi !== 'Error' && is_numeric($aqi)) {
                    $to = "923045039326"; // Or phone number if exists
                    
                    // Get city-specific message
                    $message = $this->getWhatsappMessage($aqi, $cityName);
                    
                    if ($message) {
                        dispatch(new SendWhatsappMessageJob($to, $cityName, $aqi, $message));
                        $count++;
                    }
                }
            }

            Log::info("📨 Successfully queued {$count} messages for sending.");

        } catch (Exception $exception) {
            Log::error('💥 [Whatsapp Cron Error] AQI Message Scheduler failed: ' . $exception->getMessage());
        }

        Log::info('🏁 [Whatsapp Cron End] AQI Message Scheduler finished at ' . now()->toDateTimeString());
    }

    /**
     * Get WhatsApp message for a specific city and AQI range
     */
    private function getWhatsappMessage($aqi, $cityName)
    {
        // Check if AQI is null, empty, or 'Error'
        if (is_null($aqi) || $aqi === 'Error' || $aqi === '') {
            return null;
        }

        // Ensure AQI is numeric for comparison
        $aqi = is_numeric($aqi) ? (int) $aqi : null;
        
        if (is_null($aqi)) {
            return null;
        }

        // Determine the range based on AQI
        $range = null;
        if ($aqi <= 50) {
            $range = 'good';
        } elseif ($aqi <= 100) {
            $range = 'moderate';
        } elseif ($aqi <= 150) {
            $range = 'unhealthy_sensitive';
        } elseif ($aqi <= 200) {
            $range = 'unhealthy';
        } elseif ($aqi <= 300) {
            $range = 'very_unhealthy';
        } else {
            $range = 'hazardous';
        }

        // Try to get city-specific message first
        $message = AQI::where('type', 'whatsapp')
            ->where('city', $cityName)
            ->where('range', $range)
            ->value('message');

        // If no city-specific message, use default
        if (empty($message)) {
            if ($aqi <= 50) {
                $message = "the air quality in {$cityName} is Good 😊 (AQI: {$aqi}). Enjoy your day!";
            } elseif ($aqi <= 100) {
                $message = "the air quality in {$cityName} is Moderate 🙂 (AQI: {$aqi}). It's generally okay.";
            } elseif ($aqi <= 150) {
                $message = "the air quality in {$cityName} is Unhealthy for Sensitive Groups 😷 (AQI: {$aqi}). Be careful if you have breathing issues.";
            } elseif ($aqi <= 200) {
                $message = "the air quality in {$cityName} is Unhealthy ❌ (AQI: {$aqi}). Try to limit outdoor activity.";
            } elseif ($aqi <= 300) {
                $message = "the air quality in {$cityName} is Very Unhealthy ⚠️ (AQI: {$aqi}). Consider staying indoors.";
            } else {
                $message = "the air quality in {$cityName} is Hazardous ☠️ (AQI: {$aqi}). Stay safe and avoid going outside.";
            }
        }

        return $message;
    }
}
