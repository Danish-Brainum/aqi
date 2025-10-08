<?php

namespace App\Console\Commands;

use App\Jobs\EmailJob;
use App\Models\CSV;
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
            // Fetch all records
            $records = CSV::select('email', 'message')->get();

            if ($records->isEmpty()) {
                Log::warning('⚠️ No records found in CSV table to process.');
                return;
            }

            $count = 0;

            foreach ($records as $record) {
                try {
                    dispatch(new EmailJob($record));
                    $count++;

                    Log::info("✅ Queued message for {$record->email}");
                } catch (Exception $e) {
                    Log::error("❌ Failed to queue message for {$record->email}: {$e->getMessage()}");
                }
            }

            Log::info("📨 Successfully queued {$count} messages for sending.");

        } catch (Exception $exception) {
            Log::error('💥 [Cron Error] AQI Message Scheduler failed: ' . $exception->getMessage());
        }

        Log::info('🏁 [Cron End] AQI Message Scheduler finished at ' . now()->toDateTimeString());
    }
}
