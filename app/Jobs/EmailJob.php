<?php

namespace App\Jobs;

use App\Mail\AutoReportMail;
use App\Models\CSV;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailJob implements ShouldQueue
{
    use Queueable;

    private $email;
    private $message;
    /**
     * Create a new job instance.
     */
    public function __construct(CSV $record)
    {
        $this->email = $record->email;
        $this->message = $record->message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $data = [
                'email' => $this->email,
                'message' => $this->message,
            ];

            Mail::to($this->email)->send(new AutoReportMail($data));

            Log::info("📩 Email sent successfully to {$this->email}");
        } catch (\Throwable $e) {
            Log::error("❌ Failed to send email to {$this->email}: " . $e->getMessage());
        }
    }
}
