<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\CitySeeder;
use Database\Seeders\EmailMessageSeeder;
use Database\Seeders\WhatsappMessageSeeder;
use Database\Seeders\WhatsappRecipientSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(CitySeeder::class);
        $this->call(EmailMessageSeeder::class);
        $this->call(WhatsappMessageSeeder::class);
        $this->call(WhatsappRecipientSeeder::class);

    }
}
