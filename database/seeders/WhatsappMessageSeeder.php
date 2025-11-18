<?php

namespace Database\Seeders;

use App\Models\AQI;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class WhatsappMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates WhatsApp messages for all cities and all AQI ranges.
     * Only the message body is stored (header and footer are added in the template).
     */
    public function run(): void
    {
        $this->command->info('🔄 Starting WhatsApp messages seeder...');

        // Define messages for each AQI range (only message body, no header/footer)
        // IMPORTANT: WhatsApp template parameters cannot contain newlines, tabs, or more than 4 consecutive spaces
        // Messages are stored as single-line text (newlines replaced with spaces)
        $messages = [
            'good' => "which is Good. Today’s air is fresh and safe — a great day to enjoy the outdoors! Let’s keep it that way — choose public transport, plant trees, and protect clean air.",
            
            'moderate' => "which is Moderate. Air quality is acceptable, but may affect sensitive individuals. If you feel discomfort, take it easy and stay hydrated. Let’s reduce car use and support cleaner choices.",
            
            'unhealthy_sensitive' => "which is Unhealthy for Sensitive Groups. Today’s air may cause coughing or irritation for children and elders. Limit outdoor play, wear a mask if needed, and keep windows closed. Let’s care for our loved ones together.",
            
            'unhealthy' => "which is Unhealthy. Air quality is poor today. Everyone may feel its effects. Stay indoors when possible, use air purifiers, and avoid traffic-heavy areas. Let's protect our lungs and help others do the same.",
            
            'very_unhealthy' => "which is Very Unhealthy. Breathing this air can be harmful. Let’s take extra care today. Seal windows, avoid outdoor exposure, and check on vulnerable family members. Together, we can breathe safer.",
            
            'hazardous' => "which is Hazardous. This is an air emergency. Everyone is at risk. Stay indoors, avoid all outdoor activity, and follow safety alerts. Let’s protect our breath, our health, and each other.",
        ];

        // Get all cities from the database
        $cities = City::all();

        if ($cities->isEmpty()) {
            $this->command->warn('⚠️ No cities found in the database. Please run CitySeeder first.');
            Log::warning('⚠️ [WhatsappMessageSeeder] No cities found in database');
            return;
        }

        $this->command->info("📋 Found {$cities->count()} cities to process.");

        $totalCreated = 0;
        $totalUpdated = 0;

        // Process each city
        foreach ($cities as $city) {
            $this->command->info("📝 Processing city: {$city->name}");

            // Create/update messages for each AQI range
            foreach ($messages as $range => $messageText) {
                // Sanitize message: remove newlines, tabs, and limit consecutive spaces
                // WhatsApp template parameters cannot contain newlines, tabs, or more than 4 consecutive spaces
                $sanitizedMessage = $this->sanitizeMessage($messageText);
                
                $aqiRecord = AQI::updateOrCreate(
                    [
                        'range' => $range,
                        'type' => 'whatsapp',
                        'city' => $city->name,
                    ],
                    [
                        'message' => $sanitizedMessage,
                    ]
                );

                if ($aqiRecord->wasRecentlyCreated) {
                    $totalCreated++;
                    $this->command->line("  ✅ Created: {$range} range for {$city->name}");
                } else {
                    $totalUpdated++;
                    $this->command->line("  🔄 Updated: {$range} range for {$city->name}");
                }
            }
        }

        $totalRecords = $totalCreated + $totalUpdated;
        $this->command->info("✅ WhatsApp messages seeder completed!");
        $this->command->info("📊 Summary:");
        $this->command->info("   - Cities processed: {$cities->count()}");
        $this->command->info("   - Records created: {$totalCreated}");
        $this->command->info("   - Records updated: {$totalUpdated}");
        $this->command->info("   - Total records: {$totalRecords}");

        Log::info("✅ [WhatsappMessageSeeder] Completed: {$cities->count()} cities, {$totalCreated} created, {$totalUpdated} updated");
    }

    /**
     * Sanitize message text for WhatsApp template parameters
     * WhatsApp doesn't allow: newlines (\n), tabs (\t), or more than 4 consecutive spaces
     * 
     * @param string $message
     * @return string
     */
    private function sanitizeMessage(string $message): string
    {
        if (empty($message)) {
            return '';
        }

        // Replace newlines and tabs with single space
        $sanitized = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $message);
        
        // Replace multiple consecutive spaces (more than 1) with single space
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        
        // Trim leading and trailing spaces
        $sanitized = trim($sanitized);
        
        // Ensure no more than 4 consecutive spaces (extra safety check)
        $sanitized = preg_replace('/ {5,}/', '    ', $sanitized);
        
        return $sanitized;
    }
}

