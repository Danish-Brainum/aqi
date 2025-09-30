<?php

namespace App\Jobs;

use App\Models\City;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class FetchAqiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $cityName;
    public $state;

    public function __construct($cityName, $state)
    {
        $this->cityName = $cityName;
        $this->state    = $state;
    }

    public function handle()
    {
        $city = City::where('name', $this->cityName)
                    ->where('state', $this->state)
                    ->first();
    
        if (! $city) {
            return;
        }
    
        $city->update(['status' => 'processing']);
    
        $response = Http::get("http://api.airvisual.com/v2/city", [
            'city'    => ($this->cityName),
            'state'   => ($this->state),
            'country' => 'Pakistan',
            'key'     => env("IQAIR_API_KEY"),
        ]);
    
        if ($response->successful() && isset($response['data']['current']['pollution']['aqius'])) {
            $aqi = $response['data']['current']['pollution']['aqius'];
    
            $city->update([
                'aqi'    => $aqi,
                'status' => 'done',
            ]);
        } else {
            $city->update(['status' => 'error']);
        }
}
}