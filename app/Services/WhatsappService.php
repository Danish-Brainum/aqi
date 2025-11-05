<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $token;
    protected string $phoneNumberId;
    protected string $base;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phoneNumberId');
        $version = config('services.whatsapp.version'); // use v22.0 or whatever your curl used
        $this->base = "https://graph.facebook.com/{$version}/{$this->phoneNumberId}/messages";
    }

    public function sendText(string $to, string $text): array
    {
        $resp = Http::withToken($this->token)
            ->post($this->base, [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['body' => $text],
            ]);

        $json = $resp->json();
        Log::info('WhatsApp sendText response', ['to' => $to, 'response' => $json]);

        if ($resp->failed()) {
            // handle error (throw or return)
            return ['error' => $json];
        }

        return $json;
    }

    /**
     * Send a template message.
     *
     * @param string $to phone number as string (e.g. 9212345612)
     * @param string $templateName the EXACT approved template name
     * @param string $languageCode e.g. en_US
     * @param array $components components array per Meta specs (body parameters, header, etc.)
     * @return array response json
     */
    public function sendTemplate(string $to, string $templateName, string $languageCode = 'en', array $components = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
                'components' => $components,
            ],
        ];

        $resp = Http::withToken($this->token)
        ->withOptions([
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
        ])
        ->post($this->base, $payload);

        $json = $resp->json();
        Log::info('WhatsApp sendTemplate response', ['to' => $to, 'template' => $templateName, 'response' => $json]);

        if ($resp->failed()) {
            return ['error' => $json];
        }

        return $json;
    }
}
