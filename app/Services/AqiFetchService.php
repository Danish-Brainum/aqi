<?php

namespace App\Services;

use App\Jobs\FetchAqiJob;
use App\Models\City;
use Illuminate\Support\Facades\Log;

class AqiFetchService
{
    /**
     * Reset all cities and dispatch AQI fetch jobs with 1-minute delays
     * 
     * @return array Returns ['dispatched_count' => int, 'estimated_time' => int]
     */
    public static function fetchAllCities(): array
    {
        $cities = City::all();

        if ($cities->isEmpty()) {
            Log::warning('⚠️ [AqiFetchService] No cities found to update');
            return [
                'dispatched_count' => 0,
                'estimated_time' => 0,
                'success' => false
            ];
        }

        // Reset ALL cities: Set AQI to null and status to pending
        // This ensures old values are cleared before fetching new ones
        City::query()->update([
            'aqi' => null,
            'status' => 'pending'
        ]);

        Log::info("🔄 [AqiFetchService] Reset all cities: AQI set to null, status set to pending");

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
        
        Log::info("📤 [AqiFetchService] Dispatched {$dispatchedCount} AQI fetch jobs with 1-minute delays (estimated time: {$estimatedTime} minutes)");

        return [
            'dispatched_count' => $dispatchedCount,
            'estimated_time' => $estimatedTime,
            'success' => true
        ];
    }
}

