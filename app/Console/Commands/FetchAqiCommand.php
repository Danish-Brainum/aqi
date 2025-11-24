<?php

namespace App\Console\Commands;

use App\Services\AqiFetchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

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
            
            // Check queue connection and verify jobs
            $queueConnection = config('queue.default');
            $this->info("📊 Queue connection: {$queueConnection}");
            
            if ($queueConnection === 'database') {
                try {
                    $jobsCount = DB::table('jobs')
                        ->where('queue', 'aqi-fetch')
                        ->count();
                    
                    $this->info("✅ Verified: {$jobsCount} jobs found in 'aqi-fetch' queue in database");
                    
                    if ($jobsCount === 0 && $result['dispatched_count'] > 0) {
                        $this->warn('⚠️  WARNING: Jobs were dispatched but not found in database!');
                        $this->warn('⚠️  This might indicate a queue configuration issue.');
                    } elseif ($jobsCount > 0) {
                        $this->info('');
                        $this->warn('⚠️  IMPORTANT: Make sure a queue worker is running to process these jobs!');
                        $this->info('   Run: php artisan queue:work database --queue=aqi-fetch');
                        $this->info('   Or: php artisan queue:listen database --queue=aqi-fetch');
                    }
                } catch (\Exception $e) {
                    $this->warn("⚠️  Could not verify jobs in database: {$e->getMessage()}");
                }
            } elseif ($queueConnection === 'sync') {
                $this->info('ℹ️  Using sync queue - jobs will execute immediately');
            } else {
                $this->warn("⚠️  Queue connection is '{$queueConnection}' - make sure queue worker is configured correctly");
            }
            
            $this->info('✅ AQI fetch process started successfully!');
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            Log::error("💥 [FetchAqiCommand] Error: {$e->getMessage()}");
            return 1;
        }
    }
}
