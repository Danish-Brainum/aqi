<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappRecipient extends Model
{
    protected $fillable = ['phone', 'name', 'active'];

    /**
     * Get all active recipients
     */
    public static function getActiveRecipients(): array
    {
        return self::where('active', true)
            ->pluck('phone')
            ->toArray();
    }
}
