<?php

namespace App\Console\Commands;

use App\Jobs\EmailJob;
use App\Models\City;
use App\Models\CSV;
use App\Models\AQI;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:email-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto AQI message sent to Email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('📢 [Email Cron Start] AQI Message Scheduler triggered at ' . now()->toDateTimeString());

        try {
            // Get all records from CSV table (similar to sendEmails method)
            $csvRecords = CSV::all();

            if ($csvRecords->isEmpty()) {
                Log::warning('⚠️ No records found in CSV table to process.');
                return;
            }

            $queuedCount = 0;
            $updatedCount = 0;
            $errors = [];

            // Process each CSV record
            foreach ($csvRecords as $record) {
                try {
                    // Skip if email is empty
                    if (empty($record->email)) {
                        continue;
                    }

                    // Get city name from CSV record
                    $cityName = $record->city;

                    // Get AQI value from Cities table (similar to WhatsApp)
                    $city = City::where('name', $cityName)->first();
                    
                    if ($city && $city->aqi !== null && is_numeric($city->aqi)) {
                        // Update CSV record with AQI value from Cities table
                        $aqi = (int) $city->aqi;
                        
                        // Generate message based on AQI value
                        $message = $this->getMessage($aqi, $record->name, $cityName);
                        
                        // Update CSV record with latest AQI and message
                        $record->update([
                            'aqi' => $aqi,
                            'message' => $message
                        ]);
                        
                        $updatedCount++;
                    } else {
                        // If city not found or AQI is null, use existing message or generate fallback
                        if (empty($record->message)) {
                            $message = "Hi {$record->name}, we couldn't retrieve air quality data for {$cityName}.";
                            $record->update(['message' => $message]);
                        } else {
                            $message = $record->message;
                        }
                        // Note: AQI field is not updated if city not found - keeps existing value
                    }

                    // Dispatch email job with updated record
                    dispatch(new EmailJob($record));
                    $queuedCount++;

                    $aqiValue = ($city && $city->aqi !== null) ? $city->aqi : 'N/A';
                    Log::info("✅ Queued email for {$record->email} (city: {$cityName}, AQI: {$aqiValue})");

                } catch (Exception $e) {
                    $errors[] = "Failed to process {$record->email}: " . $e->getMessage();
                    Log::error("❌ Failed to queue message for {$record->email}: {$e->getMessage()}");
                }
            }

            Log::info("📨 Successfully queued {$queuedCount} message(s). {$updatedCount} record(s) updated with latest AQI values from Cities table.");

            if (!empty($errors)) {
                Log::warning("⚠️ Encountered " . count($errors) . " error(s) during processing.");
            }

        } catch (Exception $exception) {
            Log::error('💥 [Cron Error] AQI Message Scheduler failed: ' . $exception->getMessage());
        }

        Log::info('🏁 [Cron End] AQI Message Scheduler finished at ' . now()->toDateTimeString());
    }

    /**
     * Get message based on AQI value (same logic as AQIController)
     */
    private function getMessage($aqi, $name, $city)
    {
        if (is_null($aqi)) {
            return "Hi {$name}, we couldn't retrieve air quality data for {$city}.";
        }
        
        // Load custom messages from DB
        $messages = AQI::where('type', 'email')
            ->whereNull('city') // Get global messages
            ->pluck('message', 'range')
            ->toArray();

        // Determine AQI range
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

        // Get message body from database, or use fallback
        $messageBody = $messages[$range] ?? null;
        
        if ($messageBody) {
            return "The air quality index in {$city} is {$aqi}, {$messageBody}";
        }

        // Fallback messages if database doesn't have them
        $fallbackMessages = [
            'good' => "Today's air is fresh and safe. A great day to enjoy the outdoors!\n\nLet's keep it that way — choose public transport, plant trees, and protect clean air.",
            'moderate' => "Air quality is acceptable, but may affect sensitive individuals.\n\nIf you feel discomfort, take it easy and stay hydrated.\n\nLet's reduce car use and support cleaner choices.",
            'unhealthy_sensitive' => "Today's air may cause coughing or irritation for children and elders.\n\nLimit outdoor play, wear a mask if needed, and keep windows closed.\n\nLet's care for our loved ones together.",
            'unhealthy' => "Air quality is poor today. Everyone may feel its effects.\n\nStay indoors when possible, use air purifiers, and avoid traffic-heavy areas.\n\nLet's protect our lungs and help others do the same.",
            'very_unhealthy' => "Breathing this air can be harmful. Let's take extra care today.\n\nSeal windows, avoid outdoor exposure, and check on vulnerable family members.\n\nTogether, we can breathe safer.",
            'hazardous' => "This is an air emergency. Everyone is at risk.\n\nStay indoors, avoid all outdoor activity, and follow safety alerts.\n\nLet's protect our breath, our health, and each other.",
        ];

        return $fallbackMessages[$range] ?? "";
    }
}
