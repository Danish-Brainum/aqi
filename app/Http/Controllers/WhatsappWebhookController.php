<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsappWebhookController extends Controller
{
    /**
     * Handle webhook verification from Meta
     * Meta sends a GET request to verify the webhook
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = config('services.whatsapp.webhook_verify_token', env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'));

        Log::info('WhatsApp Webhook Verification Request', [
            'mode' => $mode,
            'token_received' => $token,
            'expected_token' => $verifyToken ? 'set' : 'not set',
        ]);

        // Verify that the mode is 'subscribe' and the token matches
        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('✅ WhatsApp Webhook verified successfully');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('❌ WhatsApp Webhook verification failed', [
            'mode' => $mode,
            'token_match' => $token === $verifyToken,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming WhatsApp messages
     * Meta sends POST requests when messages are received
     */
    public function receive(Request $request)
    {
        try {
            $data = $request->all();
            
            Log::info('📩 WhatsApp Webhook Received', [
                'payload' => $data,
            ]);

            // Check if this is a valid WhatsApp webhook
            if (!isset($data['object']) || $data['object'] !== 'whatsapp_business_account') {
                Log::warning('Invalid webhook object', ['object' => $data['object'] ?? 'missing']);
                return response()->json(['status' => 'ignored'], 200);
            }

            // Process each entry in the webhook payload
            if (isset($data['entry'])) {
                foreach ($data['entry'] as $entry) {
                    $this->processEntry($entry);
                }
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('💥 WhatsApp Webhook Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Process a single webhook entry
     */
    private function processEntry(array $entry)
    {
        if (!isset($entry['changes'])) {
            return;
        }

        foreach ($entry['changes'] as $change) {
            if (isset($change['value']['messages'])) {
                // Process incoming messages
                foreach ($change['value']['messages'] as $message) {
                    $this->processMessage($message, $change['value']);
                }
            }

            if (isset($change['value']['statuses'])) {
                // Process message status updates (delivered, read, etc.)
                foreach ($change['value']['statuses'] as $status) {
                    $this->processStatus($status);
                }
            }
        }
    }

    /**
     * Process an incoming message
     */
    private function processMessage(array $message, array $value)
    {
        $from = $message['from'] ?? null;
        $messageId = $message['id'] ?? null;
        $timestamp = $message['timestamp'] ?? null;
        $type = $message['type'] ?? 'unknown';

        Log::info('📨 Processing WhatsApp Message', [
            'from' => $from,
            'message_id' => $messageId,
            'type' => $type,
            'timestamp' => $timestamp,
        ]);

        // Handle different message types
        switch ($type) {
            case 'text':
                $text = $message['text']['body'] ?? '';
                $this->handleTextMessage($from, $text, $messageId, $timestamp);
                break;

            case 'image':
                $imageId = $message['image']['id'] ?? null;
                $caption = $message['image']['caption'] ?? null;
                $this->handleImageMessage($from, $imageId, $caption, $messageId, $timestamp);
                break;

            case 'document':
                $documentId = $message['document']['id'] ?? null;
                $caption = $message['document']['caption'] ?? null;
                $this->handleDocumentMessage($from, $documentId, $caption, $messageId, $timestamp);
                break;

            case 'audio':
                $audioId = $message['audio']['id'] ?? null;
                $this->handleAudioMessage($from, $audioId, $messageId, $timestamp);
                break;

            case 'video':
                $videoId = $message['video']['id'] ?? null;
                $caption = $message['video']['caption'] ?? null;
                $this->handleVideoMessage($from, $videoId, $caption, $messageId, $timestamp);
                break;

            case 'location':
                $latitude = $message['location']['latitude'] ?? null;
                $longitude = $message['location']['longitude'] ?? null;
                $this->handleLocationMessage($from, $latitude, $longitude, $messageId, $timestamp);
                break;

            default:
                Log::info('Unhandled message type', ['type' => $type, 'message' => $message]);
                break;
        }
    }

    /**
     * Handle incoming text message
     */
    private function handleTextMessage(string $from, string $text, ?string $messageId, ?string $timestamp)
    {
        Log::info('💬 Text Message Received', [
            'from' => $from,
            'text' => $text,
            'message_id' => $messageId,
        ]);

        // TODO: Add your business logic here
        // Examples:
        // - Save message to database
        // - Process commands (e.g., "help", "subscribe", "unsubscribe")
        // - Auto-reply to messages
        // - Forward to other services

        // Example: Auto-reply to text messages
        // $this->sendAutoReply($from, $text);
    }

    /**
     * Handle incoming image message
     */
    private function handleImageMessage(string $from, ?string $imageId, ?string $caption, ?string $messageId, ?string $timestamp)
    {
        Log::info('🖼️ Image Message Received', [
            'from' => $from,
            'image_id' => $imageId,
            'caption' => $caption,
            'message_id' => $messageId,
        ]);

        // TODO: Add your business logic here
    }

    /**
     * Handle incoming document message
     */
    private function handleDocumentMessage(string $from, ?string $documentId, ?string $caption, ?string $messageId, ?string $timestamp)
    {
        Log::info('📄 Document Message Received', [
            'from' => $from,
            'document_id' => $documentId,
            'caption' => $caption,
            'message_id' => $messageId,
        ]);

        // TODO: Add your business logic here
    }

    /**
     * Handle incoming audio message
     */
    private function handleAudioMessage(string $from, ?string $audioId, ?string $messageId, ?string $timestamp)
    {
        Log::info('🎵 Audio Message Received', [
            'from' => $from,
            'audio_id' => $audioId,
            'message_id' => $messageId,
        ]);

        // TODO: Add your business logic here
    }

    /**
     * Handle incoming video message
     */
    private function handleVideoMessage(string $from, ?string $videoId, ?string $caption, ?string $messageId, ?string $timestamp)
    {
        Log::info('🎥 Video Message Received', [
            'from' => $from,
            'video_id' => $videoId,
            'caption' => $caption,
            'message_id' => $messageId,
        ]);

        // TODO: Add your business logic here
    }

    /**
     * Handle incoming location message
     */
    private function handleLocationMessage(string $from, ?float $latitude, ?float $longitude, ?string $messageId, ?string $timestamp)
    {
        Log::info('📍 Location Message Received', [
            'from' => $from,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'message_id' => $messageId,
        ]);

        // TODO: Add your business logic here
    }

    /**
     * Process message status updates (delivered, read, etc.)
     */
    private function processStatus(array $status)
    {
        $messageId = $status['id'] ?? null;
        $recipientId = $status['recipient_id'] ?? null;
        $statusType = $status['status'] ?? null;
        $timestamp = $status['timestamp'] ?? null;

        Log::info('📊 Message Status Update', [
            'message_id' => $messageId,
            'recipient_id' => $recipientId,
            'status' => $statusType,
            'timestamp' => $timestamp,
        ]);

        // TODO: Add your business logic here
        // Examples:
        // - Update message status in database
        // - Track delivery rates
        // - Handle failed messages
    }

    /**
     * Send an auto-reply message
     * This is an example method - customize as needed
     */
    private function sendAutoReply(string $to, string $receivedText)
    {
        // Example: Send auto-reply for "help" command
        if (strtolower(trim($receivedText)) === 'help') {
            $reply = "Welcome to Pulmonol! 🌿\n\nAvailable commands:\n• help - Show this message\n• subscribe - Subscribe to AQI updates";
            
            // Use your WhatsappService to send reply
            // app(WhatsappService::class)->sendText($to, $reply);
        }
    }
}

