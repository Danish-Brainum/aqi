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

        try {
            // Template format:
            // Mr. Pulmo 🌿 — Caring for You and {{1}}
            // {{2}}
            // *Your breath matters to us.*
            // Powered by Pulmo, CC
            // Where {{1}} = city name, {{2}} = message
            
            // Template name - update in .env as WHATSAPP_TEMPLATE_NAME
            $template = trim(config('services.whatsapp.template_name', 'aqi_notification'));
            $language = config('services.whatsapp.template_language', 'en');

            // Sanitize message text for WhatsApp template parameters
            // WhatsApp doesn't allow: newlines (\n), tabs (\t), or more than 4 consecutive spaces
            $sanitizedMessage = $this->sanitizeMessageForWhatsApp($this->message ?? '');
            $sanitizedCity = $this->sanitizeMessageForWhatsApp($this->city ?? '');

            $components = [
                [
                    "type" => "body",
                    "parameters" => [
                        [
                            "type" => "text",
                            "text" => $sanitizedCity,
                        ],
                        [
                            "type" => "text",
                            "text" => $sanitizedMessage,
                        ],
                    ],
                ],
            ];
            $resp = $whatsapp->sendTemplate($this->to, $template, $language, $components);
            
            if (isset($resp['messages'][0]['id'])) {
                Log::info("✅ WhatsApp message sent to {$this->to} for city {$this->city} (AQI {$this->aqi})");
            } else {
                Log::error("❌ WhatsApp message failed for {$this->to}", ['response' => $resp]);
                
                // Log error details if available
                if (isset($resp['error'])) {
                    Log::error("WhatsApp API Error: " . json_encode($resp['error']));
                }
            }
        } catch (Exception $exception) {
            Log::error('💥 Whatsapp error: ' . $exception->getMessage());
            Log::error('💥 Whatsapp error trace: ' . $exception->getTraceAsString());
        }
    }

    /**
     * Sanitize message text for WhatsApp template parameters
     * WhatsApp doesn't allow: newlines (\n), tabs (\t), or more than 4 consecutive spaces
     * 
     * @param string $message
     * @return string
     */
    private function sanitizeMessageForWhatsApp(string $message): string
    {
        if (empty($message)) {
            return '';
        }

        // Replace newlines and tabs with single space
        $sanitized = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $message);
        
        // Replace multiple consecutive spaces (more than 1) with single space
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        
        // Trim leading and trailing spaces
        $sanitized = trim($sanitized);
        
        // Ensure no more than 4 consecutive spaces (extra safety check)
        $sanitized = preg_replace('/ {5,}/', '    ', $sanitized);
        
        return $sanitized;
    }
}
