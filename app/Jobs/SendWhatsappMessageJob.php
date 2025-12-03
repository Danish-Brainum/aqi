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
        $this->onQueue('whatsapp');
        $this->to = $to;
        $this->city = $name;
        $this->aqi = $aqi;
        $this->message = $message;
    }

    public function handle(WhatsappService $whatsapp)
    {

        try {
            // Template format:
            // The air quality index in {{1}} is {{2}}, {{3}}.
            //
            // *Your breath matters to us.*
            // Where {{1}} = city name, {{2}} = AQI value, {{3}} = message
            
            // Template name - update in .env as WHATSAPP_TEMPLATE_NAME
            $template = trim(config('services.whatsapp.template_name', 'pulmonol'));
            $language = config('services.whatsapp.template_language', 'en');

            // Sanitize message text for WhatsApp template parameters
            // WhatsApp doesn't allow: newlines (\n), tabs (\t), or more than 4 consecutive spaces
            $sanitizedMessage = $this->sanitizeMessageForWhatsApp($this->message ?? '');
            $sanitizedCity = $this->sanitizeMessageForWhatsApp($this->city ?? '');
            $sanitizedAqi = (string) ($this->aqi ?? '');

            $components = [
                [
                    "type" => "body",
                    "parameters" => [
                        [
                            "type" => "text",
                            "text" => $sanitizedCity,  // {{1}} = city
                        ],
                        [
                            "type" => "text",
                            "text" => $sanitizedAqi,   // {{2}} = AQI
                        ],
                        [
                            "type" => "text",
                            "text" => $sanitizedMessage, // {{3}} = message
                        ],
                    ],
                ],
            ];
            $resp = $whatsapp->sendTemplate($this->to, $template, $language, $components);
            
            if (isset($resp['messages'][0]['id'])) {
                Log::info("✅ WhatsApp message sent to {$this->to} for city {$this->city} (AQI {$this->aqi})");
            } else {
                // Check if it's an API error (immediate failure)
                if (isset($resp['error'])) {
                    $errorCode = $resp['error']['code'] ?? null;
                    $errorMessage = $resp['error']['message'] ?? 'Unknown error';
                    
                    // Handle specific error codes
                    if ($errorCode == 131049) {
                        Log::warning("⚠️ WhatsApp message blocked for {$this->to} - Recipient may have blocked or reported as spam. Error: {$errorMessage}");
                    } else {
                        Log::error("❌ WhatsApp API error for {$this->to}: [{$errorCode}] {$errorMessage}", [
                            'error' => $resp['error'],
                            'city' => $this->city,
                            'aqi' => $this->aqi,
                        ]);
                    }
                } else {
                    Log::error("❌ WhatsApp message failed for {$this->to} - Unknown error", ['response' => $resp]);
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
