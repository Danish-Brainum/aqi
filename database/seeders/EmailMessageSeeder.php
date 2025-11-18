<?php

namespace Database\Seeders;

use App\Models\AQI;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class EmailMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates email messages for all AQI ranges.
     * Only the message body is stored (header and footer are added in the email template).
     */
    public function run(): void
    {
        $this->command->info('🔄 Starting Email messages seeder...');

        // Define messages for each AQI range (only message body, no header/footer)
        // Header: "🌿 Mr. Pulmo — Caring for You" (used as email subject)
        // Footer: "Your breath matters to us.\nPowered by Pulmonol, CCL Pakistan" (added in email template)
        $messages = [
            'good' => "Today's air is fresh and safe. A great day to enjoy the outdoors! Let's keep it that way — choose public transport, plant trees, and protect clean air.",
            
            'moderate' => "Air quality is acceptable, but may affect sensitive individuals. If you feel discomfort, take it easy and stay hydrated. Let's reduce car use and support cleaner choices.",
            
            'unhealthy_sensitive' => "Today's air may cause coughing or irritation for children and elders. Limit outdoor play, wear a mask if needed, and keep windows closed. Let's care for our loved ones together.",
            
            'unhealthy' => "Air quality is poor today. Everyone may feel its effects. Stay indoors when possible, use air purifiers, and avoid traffic-heavy areas. Let's protect our lungs and help others do the same.",
            
            'very_unhealthy' => "Breathing this air can be harmful. Let's take extra care today. Seal windows, avoid outdoor exposure, and check on vulnerable family members. Together, we can breathe safer.",
            
            'hazardous' => "This is an air emergency. Everyone is at risk. Stay indoors, avoid all outdoor activity, and follow safety alerts. Let's protect our breath, our health, and each other.",
        ];

        $totalCreated = 0;
        $totalUpdated = 0;

        // Create/update messages for each AQI range (global, no city-specific)
        foreach ($messages as $range => $messageText) {
            $aqiRecord = AQI::updateOrCreate(
                [
                    'range' => $range,
                    'type' => 'email',
                    'city' => null, // Global messages, not city-specific
                ],
                [
                    'message' => $messageText,
                ]
            );

            if ($aqiRecord->wasRecentlyCreated) {
                $totalCreated++;
                $this->command->line("  ✅ Created: {$range} range");
            } else {
                $totalUpdated++;
                $this->command->line("  🔄 Updated: {$range} range");
            }
        }

        $totalRecords = $totalCreated + $totalUpdated;
        $this->command->info("✅ Email messages seeder completed!");
        $this->command->info("📊 Summary:");
        $this->command->info("   - Records created: {$totalCreated}");
        $this->command->info("   - Records updated: {$totalUpdated}");
        $this->command->info("   - Total records: {$totalRecords}");

        Log::info("✅ [EmailMessageSeeder] Completed: {$totalCreated} created, {$totalUpdated} updated");
    }
}
