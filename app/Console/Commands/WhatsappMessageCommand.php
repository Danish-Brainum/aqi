<?php

namespace App\Console\Commands;

use App\Jobs\EmailJob;
use App\Jobs\SendWhatsappMessageJob;
use App\Models\AQI;
use App\Models\City;
use App\Models\WhatsappRecipient;
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

            // Get all active WhatsApp recipients
            $recipients = WhatsappRecipient::getActiveRecipients();
            
            if (empty($recipients)) {
                Log::warning('No WhatsApp recipients found. Please add recipients first.');
                return;
            }

            $totalMessagesQueued = 0;
            foreach ($cities as $row) {
                // Access as object property, not array
                $aqi = $row->aqi;
                $cityName = $row->name;

                // Check if AQI is valid (not null, not 'Error', and numeric)
                if ($aqi !== null && $aqi !== 'Error' && is_numeric($aqi)) {
                    // Get city-specific message
                    $message = $this->getWhatsappMessage($aqi, $cityName);

                    if ($message) {
                        // Send to all recipients
                        foreach ($recipients as $phoneNumber) {
                            dispatch(new SendWhatsappMessageJob($phoneNumber, $cityName, $aqi, $message));
                            $totalMessagesQueued++;
                        }
                    }
                }
            }

            $recipientCount = count($recipients);
            Log::info("📨 Successfully queued {$totalMessagesQueued} messages for sending to {$recipientCount} recipient(s).");

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
        // Note: City and AQI are now separate template parameters, so message should not include them
        if (empty($message)) {
            if ($aqi <= 50) {
                $message = "which is Good. Today's air is fresh and safe — a great day to enjoy the outdoors! Let's keep it that way — choose public transport, plant trees, and protect clean air.";
            } elseif ($aqi <= 100) {
                $message = "which is Moderate. Air quality is acceptable, but may affect sensitive individuals. If you feel discomfort, take it easy and stay hydrated. Let's reduce car use and support cleaner choices.";
            } elseif ($aqi <= 150) {
                $message = "which is Unhealthy for Sensitive Groups. Today's air may cause coughing or irritation for children and elders. Limit outdoor play, wear a mask if needed, and keep windows closed. Let's care for our loved ones together.";
            } elseif ($aqi <= 200) {
                $message = "which is Unhealthy. Air quality is poor today. Everyone may feel its effects. Stay indoors when possible, use air purifiers, and avoid traffic-heavy areas. Let's protect our lungs and help others do the same.";
            } elseif ($aqi <= 300) {
                $message = "which is Very Unhealthy. Breathing this air can be harmful. Let's take extra care today. Seal windows, avoid outdoor exposure, and check on vulnerable family members. Together, we can breathe safer.";
            } else {
                $message = "which is Hazardous. This is an air emergency. Everyone is at risk. Stay indoors, avoid all outdoor activity, and follow safety alerts. Let's protect our breath, our health, and each other.";
            }
        }

        return $message;
    }
}
