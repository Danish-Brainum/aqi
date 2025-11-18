<?php

namespace Database\Seeders;

use App\Models\WhatsappRecipient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class WhatsappRecipientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates WhatsApp recipients.
     * You can add multiple phone numbers here.
     */
    public function run(): void
    {
        $this->command->info('🔄 Starting WhatsApp recipients seeder...');

        // Define recipients (phone numbers should be in international format without + sign)
        // Example: 923073017101 (for Pakistan: 92 is country code, 3073017101 is the number)
        $recipients = [
            ['phone' => '923073017101', 'name' => 'Default Recipient', 'active' => true],
            // Add more recipients here as needed
            // ['phone' => '923001234567', 'name' => 'Recipient 2', 'active' => true],
            // ['phone' => '923009876543', 'name' => 'Recipient 3', 'active' => true],
        ];

        $totalCreated = 0;
        $totalUpdated = 0;

        foreach ($recipients as $recipient) {
            $recipientRecord = WhatsappRecipient::updateOrCreate(
                ['phone' => $recipient['phone']],
                [
                    'name' => $recipient['name'],
                    'active' => $recipient['active'],
                ]
            );

            if ($recipientRecord->wasRecentlyCreated) {
                $totalCreated++;
                $this->command->line("  ✅ Created: {$recipient['phone']} ({$recipient['name']})");
            } else {
                $totalUpdated++;
                $this->command->line("  🔄 Updated: {$recipient['phone']} ({$recipient['name']})");
            }
        }

        $totalRecords = $totalCreated + $totalUpdated;
        $this->command->info("✅ WhatsApp recipients seeder completed!");
        $this->command->info("📊 Summary:");
        $this->command->info("   - Recipients created: {$totalCreated}");
        $this->command->info("   - Recipients updated: {$totalUpdated}");
        $this->command->info("   - Total recipients: {$totalRecords}");

        Log::info("✅ [WhatsappRecipientSeeder] Completed: {$totalCreated} created, {$totalUpdated} updated");
    }
}
