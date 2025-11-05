<?php

namespace App\Console\Commands;

use App\Services\AqiFetchService;
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
            $result = AqiFetchService::fetchAllCities();

            if (!$result['success'] || $result['dispatched_count'] === 0) {
                $this->warn('⚠️ No cities found to update.');
                return 1;
            }

            $this->info('✅ Reset all cities: AQI set to null, status set to pending');
            $this->info("📤 Dispatched {$result['dispatched_count']} AQI fetch jobs with 1-minute delays");
            $this->info("⏱️  Estimated completion time: {$result['estimated_time']} minutes");
            $this->info('✅ AQI fetch process started successfully!');
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            Log::error("💥 [FetchAqiCommand] Error: {$e->getMessage()}");
            return 1;
        }
    }
}
