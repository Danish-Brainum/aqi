<?php

namespace App\Repositories;

use App\Models\Aqi;

class AQIRepository
{
    public function getEmailMessages(): array
    {
        return Aqi::where('type', 'email')->pluck('message', 'range')->toArray();
    }

    public function getWhatsappMessages(): array
    {
        return Aqi::where('type', 'whatsapp')->pluck('message', 'range')->toArray();
    }
    
}
