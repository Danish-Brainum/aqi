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
        $messages = [
            'good' => "Today's air is fresh and safe. A great day to enjoy the outdoors!\n\nLet's keep it that way — choose public transport, plant trees, and protect clean air.",
            
            'moderate' => "Air quality is acceptable, but may affect sensitive individuals.\n\nIf you feel discomfort, take it easy and stay hydrated.\n\nLet's reduce car use and support cleaner choices.",
            
            'unhealthy_sensitive' => "Today's air may cause coughing or irritation for children and elders.\n\nLimit outdoor play, wear a mask if needed, and keep windows closed.\n\nLet's care for our loved ones together.",
            
            'unhealthy' => "Air quality is poor today. Everyone may feel its effects.\n\nStay indoors when possible, use air purifiers, and avoid traffic-heavy areas.\n\nLet's protect our lungs and help others do the same.",
            
            'very_unhealthy' => "Breathing this air can be harmful. Let's take extra care today.\n\nSeal windows, avoid outdoor exposure, and check on vulnerable family members.\n\nTogether, we can breathe safer.",
            
            'hazardous' => "This is an air emergency. Everyone is at risk.\n\nStay indoors, avoid all outdoor activity, and follow safety alerts.\n\nLet's protect our breath, our health, and each other.",
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
                $aqiRecord = AQI::updateOrCreate(
                    [
                        'range' => $range,
                        'type' => 'whatsapp',
                        'city' => $city->name,
                    ],
                    [
                        'message' => $messageText,
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
}

