<?php

namespace App\Console\Commands;

use App\Jobs\FetchAqiJob;
use App\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchAqiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aqi:fetch-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch AQI values for all cities with 1-minute delays between requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting AQI fetch for all cities...');
        
        try {
            $cities = City::all();

            if ($cities->isEmpty()) {
                $this->warn('⚠️ No cities found to update.');
                Log::warning('⚠️ [FetchAqiCommand] No cities found to update');
                return 1;
            }

            // Reset ALL cities: Set AQI to null and status to pending
            // This ensures old values are cleared before fetching new ones
            City::query()->update([
                'aqi' => null,
                'status' => 'pending'
            ]);

            $this->info('✅ Reset all cities: AQI set to null, status set to pending');
            Log::info("🔄 [FetchAqiCommand] Reset all cities: AQI set to null, status set to pending");

            $delayMinutes = 0;
            $dispatchedCount = 0;

            foreach ($cities as $city) {
                // Dispatch job with delay to ensure sequential processing
                // Each job is spaced 1 minute (60 seconds) apart to respect API rate limit (5 requests/minute)
                dispatch(new FetchAqiJob($city->name, $city->state))
                    ->delay(now()->addMinutes($delayMinutes))
                    ->onQueue('aqi-fetch'); // Use a dedicated queue for better control

                $delayMinutes += 1; // Space each job by 1 minute to respect API rate limit
                $dispatchedCount++;
            }

            $estimatedTime = $dispatchedCount > 0 ? round($dispatchedCount * 1) : 0;
            
            $this->info("📤 Dispatched {$dispatchedCount} AQI fetch jobs with 1-minute delays");
            $this->info("⏱️  Estimated completion time: {$estimatedTime} minutes");
            
            Log::info("📤 [FetchAqiCommand] Dispatched {$dispatchedCount} AQI fetch jobs with 1-minute delays (estimated time: {$estimatedTime} minutes)");

            $this->info('✅ AQI fetch process started successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            Log::error("💥 [FetchAqiCommand] Error: {$e->getMessage()}");
            return 1;
        }
    }
}
