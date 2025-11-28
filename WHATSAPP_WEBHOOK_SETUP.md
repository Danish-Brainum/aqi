# WhatsApp Webhook Setup Guide

This guide explains how to set up the WhatsApp webhook to receive incoming messages.

## Overview

The webhook allows your application to receive messages sent to your WhatsApp Business number. It handles:
- ✅ Webhook verification (Meta requires this to activate the webhook)
- ✅ Incoming text messages
- ✅ Incoming media messages (images, videos, documents, audio)
- ✅ Location messages
- ✅ Message status updates (delivered, read, failed, etc.)

## Prerequisites

1. WhatsApp Business API account set up with Meta
2. Your application deployed and accessible via HTTPS
3. `.env` file configured with WhatsApp credentials

## Step 1: Configure Environment Variables

Add these to your `.env` file:

```env
WHATSAPP_CLOUD_TOKEN=your_access_token_here
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id_here
WHATSAPP_API_VERSION=v22.0
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_custom_verify_token_here
```

**Important:** 
- `WHATSAPP_WEBHOOK_VERIFY_TOKEN` should be a random, secure string (e.g., `generate-random-string-here-12345`)
- Use the same token when configuring the webhook in Meta's dashboard

## Step 2: Webhook URL

Your webhook URL will be:
```
https://yourdomain.com/webhook/whatsapp
```

**Note:** 
- Use HTTPS (required by Meta)
- Replace `yourdomain.com` with your actual domain

## Step 3: Configure Webhook in Meta Dashboard

1. Go to [Meta for Developers](https://developers.facebook.com/)
2. Navigate to your WhatsApp Business App
3. Go to **WhatsApp** → **Configuration**
4. Under **Webhook**, click **Edit**
5. Enter your webhook URL: `https://yourdomain.com/webhook/whatsapp`
6. Enter your verify token (same as `WHATSAPP_WEBHOOK_VERIFY_TOKEN` in `.env`)
7. Click **Verify and Save**

### Webhook Fields

Subscribe to these webhook fields:
- ✅ **messages** - Receive incoming messages
- ✅ **message_status** - Receive delivery/read status updates
- ✅ **message_template_status_update** - Receive template message status updates (optional)

## Step 4: Test the Webhook

### Verify Webhook is Working

Meta will send a GET request to verify your webhook. Check your logs:

```bash
tail -f storage/logs/laravel.log
```

You should see:
```
✅ WhatsApp Webhook verified successfully
```

### Test with a Message

1. Send a message to your WhatsApp Business number from any phone
2. Check the logs to see if the message was received:
   ```bash
   tail -f storage/logs/laravel.log | grep "WhatsApp"
   ```

3. You should see logs like:
   ```
   📩 WhatsApp Webhook Received
   📨 Processing WhatsApp Message
   💬 Text Message Received
   ```

## Step 5: Customize Message Handling

Edit `app/Http/Controllers/WhatsappWebhookController.php` to add your business logic:

### Handle Text Messages

Update the `handleTextMessage()` method:

```php
private function handleTextMessage(string $from, string $text, ?string $messageId, ?string $timestamp)
{
    Log::info('💬 Text Message Received', [
        'from' => $from,
        'text' => $text,
        'message_id' => $messageId,
    ]);

    // Example: Auto-reply
    if (strtolower(trim($text)) === 'help') {
        $whatsapp = app(\App\Services\WhatsappService::class);
        $whatsapp->sendText($from, "Welcome! Use 'help' for assistance.");
    }

    // Example: Save to database
    // IncomingMessage::create([
    //     'from' => $from,
    //     'message' => $text,
    //     'message_id' => $messageId,
    //     'received_at' => now(),
    // ]);
}
```

### Handle Other Message Types

The controller already has methods for:
- `handleImageMessage()` - Images
- `handleVideoMessage()` - Videos
- `handleDocumentMessage()` - Documents
- `handleAudioMessage()` - Audio messages
- `handleLocationMessage()` - Location sharing
- `processStatus()` - Message delivery status

## Routes

Two routes are created:

1. **GET** `/webhook/whatsapp` - Webhook verification (used by Meta)
2. **POST** `/webhook/whatsapp` - Receives incoming messages and status updates

Both routes are:
- ✅ Excluded from authentication middleware
- ✅ Excluded from CSRF protection
- ✅ Rate limited (60 requests per minute)

## Security

1. **Verify Token**: Always use a strong, random verify token
2. **HTTPS Required**: Meta requires HTTPS for webhooks
3. **Rate Limiting**: Webhook endpoint is rate-limited to prevent abuse
4. **Logging**: All webhook requests are logged for debugging

## Troubleshooting

### Webhook Verification Fails

**Problem:** Meta shows "Webhook verification failed"

**Solutions:**
1. Check that `WHATSAPP_WEBHOOK_VERIFY_TOKEN` in `.env` matches the token in Meta dashboard
2. Ensure your server is accessible via HTTPS
3. Check Laravel logs: `storage/logs/laravel.log`
4. Make sure the route is accessible: `https://yourdomain.com/webhook/whatsapp`

### Messages Not Being Received

**Problem:** Webhook verified but no messages received

**Solutions:**
1. Check that you subscribed to the correct webhook fields in Meta dashboard
2. Verify your server is accessible from the internet
3. Check Laravel logs for any errors
4. Ensure your WhatsApp Business number is active and can receive messages

### CSRF Token Mismatch Error

**Problem:** Getting 419 error on webhook

**Solutions:**
1. Verify webhook routes are excluded in `bootstrap/app.php`
2. Clear config cache: `php artisan config:clear`
3. Check that routes are defined outside auth middleware

## Example Webhook Payload

When a text message is received, you'll get a payload like:

```json
{
  "object": "whatsapp_business_account",
  "entry": [
    {
      "id": "WHATSAPP_BUSINESS_ACCOUNT_ID",
      "changes": [
        {
          "value": {
            "messaging_product": "whatsapp",
            "metadata": {
              "display_phone_number": "1555XXXXX",
              "phone_number_id": "PHONE_NUMBER_ID"
            },
            "messages": [
              {
                "from": "923001234567",
                "id": "wamid.XXX",
                "timestamp": "1234567890",
                "type": "text",
                "text": {
                  "body": "Hello!"
                }
              }
            ]
          },
          "field": "messages"
        }
      ]
    }
  ]
}
```

## Next Steps

1. ✅ Webhook is configured and receiving messages
2. ⬜ Add database table to store incoming messages (optional)
3. ⬜ Implement auto-reply logic
4. ⬜ Add command processing (e.g., "help", "subscribe", etc.)
5. ⬜ Set up notifications for important messages

## Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Check Meta's webhook documentation: https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks
- Verify webhook status in Meta dashboard

