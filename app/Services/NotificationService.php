<?php

namespace App\Services;

use App\Mail\AutoReportMail;
use App\Jobs\SendWhatsappMessageJob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendEmail(string $email, string $message): bool
    {
        try {
            Mail::to($email)->send(new AutoReportMail([
                'message' => $message,
                'subject' => 'Mr. Pulmo - Caring for You', // Header as subject
            ]));
            return true;
        } catch (\Throwable $e) {
            Log::error("Email failed to {$email}: " . $e->getMessage());
            return false;
        }
    }

    public function sendWhatsapp(string $to, string $name, $aqi, string $message): void
    {
        try {
            dispatch(new SendWhatsappMessageJob($to, $name, $aqi, $message));
        } catch (\Throwable $e) {
            Log::error("WhatsApp send failed to {$to}: " . $e->getMessage());
        }
    }
}
