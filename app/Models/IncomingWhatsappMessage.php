<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomingWhatsappMessage extends Model
{
    protected $table = 'incoming_whatsapp_messages';

    protected $fillable = [
        'from',
        'message_id',
        'type',
        'message',
        'media_id',
        'mime_type',
        'latitude',
        'longitude',
        'timestamp',
        'raw_data',
        'read',
    ];

    protected $casts = [
        'read' => 'boolean',
        'timestamp' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the formatted phone number (for display)
     */
    public function getFormattedPhoneAttribute(): string
    {
        return $this->from;
    }

    /**
     * Get the formatted timestamp (human readable)
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    /**
     * Scope to get unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope to get messages from a specific phone number
     */
    public function scopeFromPhone($query, string $phone)
    {
        return $query->where('from', $phone);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        $this->update(['read' => true]);
    }
}

