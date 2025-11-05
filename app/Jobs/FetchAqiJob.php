<?php

namespace App\Jobs;

use App\Models\City;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FetchAqiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $cityName;
    public $state;
    
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 30;

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    public function __construct($cityName, $state)
    {
        $this->cityName = $cityName;
        $this->state = $state;
    }

    public function handle()
    {
        Log::info("🌍 [FetchAqiJob] Started for {$this->cityName}, {$this->state} at " . now());

        try {
            $city = City::where('name', $this->cityName)
                        ->where('state', $this->state)
                        ->first();

            if (! $city) {
                Log::warning("⚠️ [FetchAqiJob] City not found: {$this->cityName}, {$this->state}");
                return;
            }

            // Ensure status is processing
            $city->update(['status' => 'processing']);
            Log::info("🔄 [FetchAqiJob] Status updated to 'processing' for {$this->cityName}");

            // Make HTTP request with timeout and retry settings
            $response = Http::timeout(30) // 30 second timeout
                ->retry(2, 2000) // Retry 2 times with 2 second delay between retries
                ->withOptions([
                    'allow_redirects' => true,
                    'verify' => true, // Verify SSL certificates
                ])
                ->get("https://api.airvisual.com/v2/city", [
                    'city'    => $this->cityName,
                    'state'   => $this->state,
                    'country' => 'Pakistan',
                    'key' => config('services.IQAir.token'),
                ]);

            // Check for rate limiting
            if ($response->status() === 429) {
                Log::warning("⏱️ [FetchAqiJob] Rate limited for {$this->cityName}. Retrying later...");
                $city->update(['status' => 'pending']);
                // Release job back to queue with delay
                $this->release(60); // Wait 60 seconds before retry
                return;
            }

            if ($response->failed()) {
                $errorBody = $response->body();
                Log::error("❌ [FetchAqiJob] API request failed for {$this->cityName}: Status {$response->status()} - {$errorBody}");
                
                // Check if it's an API error that shouldn't retry
                if ($response->status() === 400 || $response->status() === 404) {
                    // Bad request or not found - don't retry
                    $city->update(['status' => 'error']);
                    return;
                }
                
                // Other errors - mark as pending to retry later
                $city->update(['status' => 'pending']);
                throw new Exception("API request failed: {$errorBody}");
            }

            $data = $response->json();

            // Check for API errors in response
            if (isset($data['status']) && $data['status'] === 'fail') {
                $errorMsg = $data['data']['message'] ?? 'Unknown API error';
                Log::error("❌ [FetchAqiJob] API returned error for {$this->cityName}: {$errorMsg}");
                $city->update(['status' => 'error']);
                return;
            }

            if (isset($data['data']['current']['pollution']['aqius'])) {
                $aqi = (int) $data['data']['current']['pollution']['aqius'];

                $city->update([
                    'aqi' => $aqi,
                    'status' => 'done',
                ]);

                Log::info("✅ [FetchAqiJob] AQI for {$this->cityName}: {$aqi}");
            } else {
                Log::warning("⚠️ [FetchAqiJob] AQI data not found for {$this->cityName}. Response: " . json_encode($data));
                $city->update(['status' => 'error']);
            }
        } catch (Exception $e) {
            Log::error("💥 [FetchAqiJob] Exception for {$this->cityName}: {$e->getMessage()}");
            Log::error("💥 [FetchAqiJob] Stack trace: {$e->getTraceAsString()}");
            
            $city = City::where('name', $this->cityName)
                        ->where('state', $this->state)
                        ->first();

            if ($city) {
                // Only mark as error if we've exhausted retries
                if ($this->attempts() >= $this->tries) {
                    $city->update(['status' => 'error']);
                } else {
                    // Otherwise, mark as pending for retry
                    $city->update(['status' => 'pending']);
                }
            }
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }

        Log::info("🏁 [FetchAqiJob] Finished for {$this->cityName}, {$this->state} at " . now());
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("💥 [FetchAqiJob] Job failed permanently for {$this->cityName}, {$this->state}: {$exception->getMessage()}");
        
        $city = City::where('name', $this->cityName)
                    ->where('state', $this->state)
                    ->first();

        if ($city) {
            $city->update(['status' => 'error']);
        }
    }
}
