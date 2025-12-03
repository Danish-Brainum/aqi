<?php

namespace App\Http\Controllers;

use App\Models\IncomingWhatsappMessage;
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
            
            // Log only essential info to avoid "Over 9 levels deep" error
            $hasMessages = isset($data['entry'][0]['changes'][0]['value']['messages']);
            $hasStatuses = isset($data['entry'][0]['changes'][0]['value']['statuses']);
            
            Log::info('📩 WhatsApp Webhook Received', [
                'type' => $hasMessages ? 'incoming_message' : ($hasStatuses ? 'status_update' : 'other'),
                'entry_count' => count($data['entry'] ?? []),
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
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
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
                    try {
                        $this->processStatus($status);
                    } catch (\Exception $e) {
                        // Catch any errors during status processing to prevent webhook from failing
                        Log::error('Error processing status update: ' . $e->getMessage());
                    }
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

        // Store raw message data for reference
        $rawData = json_encode(['message' => $message, 'value' => $value]);

        // Handle different message types
        switch ($type) {
            case 'text':
                $text = $message['text']['body'] ?? '';
                $this->handleTextMessage($from, $text, $messageId, $timestamp, $rawData);
                break;

            case 'image':
                $imageId = $message['image']['id'] ?? null;
                $caption = $message['image']['caption'] ?? null;
                $mimeType = $message['image']['mime_type'] ?? null;
                $this->handleImageMessage($from, $imageId, $caption, $messageId, $timestamp, $mimeType, $rawData);
                break;

            case 'document':
                $documentId = $message['document']['id'] ?? null;
                $caption = $message['document']['caption'] ?? null;
                $mimeType = $message['document']['mime_type'] ?? null;
                $this->handleDocumentMessage($from, $documentId, $caption, $messageId, $timestamp, $mimeType, $rawData);
                break;

            case 'audio':
                $audioId = $message['audio']['id'] ?? null;
                $mimeType = $message['audio']['mime_type'] ?? null;
                $this->handleAudioMessage($from, $audioId, $messageId, $timestamp, $mimeType, $rawData);
                break;

            case 'video':
                $videoId = $message['video']['id'] ?? null;
                $caption = $message['video']['caption'] ?? null;
                $mimeType = $message['video']['mime_type'] ?? null;
                $this->handleVideoMessage($from, $videoId, $caption, $messageId, $timestamp, $mimeType, $rawData);
                break;

            case 'location':
                $latitude = $message['location']['latitude'] ?? null;
                $longitude = $message['location']['longitude'] ?? null;
                $this->handleLocationMessage($from, $latitude, $longitude, $messageId, $timestamp, $rawData);
                break;

            default:
                Log::info('Unhandled message type', ['type' => $type, 'message' => $message]);
                break;
        }
    }

    /**
     * Handle incoming text message
     */
    private function handleTextMessage(string $from, string $text, ?string $messageId, ?string $timestamp, ?string $rawData = null)
    {
        Log::info('💬 Text Message Received', [
            'from' => $from,
            'text' => $text,
            'message_id' => $messageId,
        ]);

        // Save message to database
        try {
            IncomingWhatsappMessage::updateOrCreate(
                ['message_id' => $messageId],
                [
                    'from' => $from,
                    'type' => 'text',
                    'message' => $text,
                    'timestamp' => $timestamp ?? time(),
                    'raw_data' => $rawData,
                    'read' => false,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error saving message to database: ' . $e->getMessage());
        }

        // TODO: Add your business logic here
        // Examples:
        // - Process commands (e.g., "help", "subscribe", "unsubscribe")
        // - Auto-reply to messages
        // - Forward to other services

        // Example: Auto-reply to text messages
        // $this->sendAutoReply($from, $text);
    }

    /**
     * Handle incoming image message
     */
    private function handleImageMessage(string $from, ?string $imageId, ?string $caption, ?string $messageId, ?string $timestamp, ?string $mimeType = null, ?string $rawData = null)
    {
        Log::info('🖼️ Image Message Received', [
            'from' => $from,
            'image_id' => $imageId,
            'caption' => $caption,
            'message_id' => $messageId,
        ]);

        // Save message to database
        try {
            IncomingWhatsappMessage::updateOrCreate(
                ['message_id' => $messageId],
                [
                    'from' => $from,
                    'type' => 'image',
                    'message' => $caption,
                    'media_id' => $imageId,
                    'mime_type' => $mimeType,
                    'timestamp' => $timestamp ?? time(),
                    'raw_data' => $rawData,
                    'read' => false,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error saving image message to database: ' . $e->getMessage());
        }
    }

    /**
     * Handle incoming document message
     */
    private function handleDocumentMessage(string $from, ?string $documentId, ?string $caption, ?string $messageId, ?string $timestamp, ?string $mimeType = null, ?string $rawData = null)
    {
        Log::info('📄 Document Message Received', [
            'from' => $from,
            'document_id' => $documentId,
            'caption' => $caption,
            'message_id' => $messageId,
        ]);

        // Save message to database
        try {
            IncomingWhatsappMessage::updateOrCreate(
                ['message_id' => $messageId],
                [
                    'from' => $from,
                    'type' => 'document',
                    'message' => $caption,
                    'media_id' => $documentId,
                    'mime_type' => $mimeType,
                    'timestamp' => $timestamp ?? time(),
                    'raw_data' => $rawData,
                    'read' => false,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error saving document message to database: ' . $e->getMessage());
        }
    }

    /**
     * Handle incoming audio message
     */
    private function handleAudioMessage(string $from, ?string $audioId, ?string $messageId, ?string $timestamp, ?string $mimeType = null, ?string $rawData = null)
    {
        Log::info('🎵 Audio Message Received', [
            'from' => $from,
            'audio_id' => $audioId,
            'message_id' => $messageId,
        ]);

        // Save message to database
        try {
            IncomingWhatsappMessage::updateOrCreate(
                ['message_id' => $messageId],
                [
                    'from' => $from,
                    'type' => 'audio',
                    'media_id' => $audioId,
                    'mime_type' => $mimeType,
                    'timestamp' => $timestamp ?? time(),
                    'raw_data' => $rawData,
                    'read' => false,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error saving audio message to database: ' . $e->getMessage());
        }
    }

    /**
     * Handle incoming video message
     */
    private function handleVideoMessage(string $from, ?string $videoId, ?string $caption, ?string $messageId, ?string $timestamp, ?string $mimeType = null, ?string $rawData = null)
    {
        Log::info('🎥 Video Message Received', [
            'from' => $from,
            'video_id' => $videoId,
            'caption' => $caption,
            'message_id' => $messageId,
        ]);

        // Save message to database
        try {
            IncomingWhatsappMessage::updateOrCreate(
                ['message_id' => $messageId],
                [
                    'from' => $from,
                    'type' => 'video',
                    'message' => $caption,
                    'media_id' => $videoId,
                    'mime_type' => $mimeType,
                    'timestamp' => $timestamp ?? time(),
                    'raw_data' => $rawData,
                    'read' => false,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error saving video message to database: ' . $e->getMessage());
        }
    }

    /**
     * Handle incoming location message
     */
    private function handleLocationMessage(string $from, ?float $latitude, ?float $longitude, ?string $messageId, ?string $timestamp, ?string $rawData = null)
    {
        Log::info('📍 Location Message Received', [
            'from' => $from,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'message_id' => $messageId,
        ]);

        // Save message to database
        try {
            IncomingWhatsappMessage::updateOrCreate(
                ['message_id' => $messageId],
                [
                    'from' => $from,
                    'type' => 'location',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'timestamp' => $timestamp ?? time(),
                    'raw_data' => $rawData,
                    'read' => false,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error saving location message to database: ' . $e->getMessage());
        }
    }

    /**
     * Process message status updates (delivered, read, etc.)
     */
    private function processStatus(array $status)
    {
        // Safely extract status data to avoid normalization errors
        $messageId = $status['id'] ?? null;
        $recipientId = $status['recipient_id'] ?? null;
        $statusType = $status['status'] ?? null;
        $timestamp = $status['timestamp'] ?? null;
        
        // Extract error details if message failed
        $errors = null;
        if (isset($status['errors']) && is_array($status['errors'])) {
            $errors = array_map(function($error) {
                return [
                    'code' => $error['code'] ?? null,
                    'title' => $error['title'] ?? null,
                    'message' => $error['message'] ?? null,
                    'error_data' => $error['error_data'] ?? null,
                ];
            }, $status['errors']);
        }

        if ($statusType === 'failed') {
            Log::warning('❌ WhatsApp Message Failed', [
                'message_id' => $messageId,
                'recipient_id' => $recipientId,
                'status' => $statusType,
                'timestamp' => $timestamp,
                'errors' => $errors,
            ]);
        } else {
            Log::info('📊 Message Status Update', [
                'message_id' => $messageId,
                'recipient_id' => $recipientId,
                'status' => $statusType,
                'timestamp' => $timestamp,
            ]);
        }

        // TODO: Add your business logic here
        // Examples:
        // - Update message status in database
        // - Track delivery rates
        // - Handle failed messages (retry, notify admin, etc.)
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

