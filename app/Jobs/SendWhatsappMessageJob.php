<?php

namespace App\Jobs;

use App\Models\City;
use App\Services\WhatsappService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;
    protected $city;
    protected $aqi;
    protected $message;

    public function __construct($to,  $name, $aqi, $message)
    {
        // $this->to = $city->to;
        $this->to = $to;
        $this->city = $name;
        $this->aqi = $aqi;
        $this->message = $message;
    }

    public function handle(WhatsappService $whatsapp)
    {
        
        try{
            $template = "aqi_notification";
            $language = "en";

            $components = [
                [
                    "type" => "body",
                    "parameters" => [
                        [
                            "type" => "text",
                            "text" => $this->city ?? '',
                        ],
                        [
                            "type" => "text",
                            "text" => (string)$this->aqi ?? '',
                        ],
                        [
                            "type" => "text",
                            "text" => $this->message ?? '',
                        ],
                    ],
                ],
            ];
            $resp = $whatsapp->sendTemplate($this->to, $template, $language, $components);
            if (isset($resp['messages'][0]['id'])) {
                Log::info("WhatsApp message sent to {$this->to} for {$this->city} (AQI {$this->aqi})");
            } else {
                Log::error("WhatsApp message failed for {$this->to}", ['response' => $resp]);
            }
        }catch(Exception $exception){
            Log::error('Whatsapp error messages: ' . $exception->getMessage());
        }
        
    }
}
