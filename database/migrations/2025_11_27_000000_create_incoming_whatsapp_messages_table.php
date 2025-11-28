<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incoming_whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->string('from')->index(); // Phone number
            $table->string('message_id')->unique(); // WhatsApp message ID
            $table->enum('type', ['text', 'image', 'video', 'audio', 'document', 'location', 'sticker', 'unknown'])->default('text');
            $table->text('message')->nullable(); // Text content or caption
            $table->string('media_id')->nullable(); // For images, videos, documents, audio
            $table->string('mime_type')->nullable(); // For media files
            $table->decimal('latitude', 10, 8)->nullable(); // For location messages
            $table->decimal('longitude', 11, 8)->nullable(); // For location messages
            $table->bigInteger('timestamp'); // WhatsApp timestamp
            $table->text('raw_data')->nullable(); // Store full webhook payload for reference
            $table->boolean('read')->default(false); // Mark as read/unread
            $table->timestamps();

            // Indexes for faster queries
            $table->index(['from', 'created_at']);
            $table->index('read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_whatsapp_messages');
    }
};

