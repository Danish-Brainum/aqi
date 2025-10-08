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

            Log::info("🔄 [FetchAqiJob] Status updated to 'processing' for {$this->cityName}");

            $response = Http::withOptions(['allow_redirects' => true])
            ->get("https://api.airvisual.com/v2/city", [
                'city'    => $this->cityName,
                'state'   => $this->state,
                'country' => 'Pakistan',
                'key' => config('services.IQAir.token'),
            ]);

            if ($response->failed()) {
                Log::error("❌ [FetchAqiJob] API request failed for {$this->cityName}: " . $response->body());
                $city->update(['status' => 'error']);
                return;
            }

            $data = $response->json();

            if (isset($data['data']['current']['pollution']['aqius'])) {
                $aqi = $data['data']['current']['pollution']['aqius'];

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
            $city = City::where('name', $this->cityName)
                        ->where('state', $this->state)
                        ->first();

            if ($city) {
                $city->update(['status' => 'error']);
            }
        }

        Log::info("🏁 [FetchAqiJob] Finished for {$this->cityName}, {$this->state} at " . now());
    }
}
