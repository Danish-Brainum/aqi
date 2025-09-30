<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            // Punjab
            ['name' => 'Bahawalpur', 'state' => 'Punjab'],
            ['name' => 'Chak Jhumra', 'state' => 'Punjab'],
            ['name' => 'Eminabad', 'state' => 'Punjab'],
            ['name' => 'Faisalabad', 'state' => 'Punjab'],
            ['name' => 'Gujranwala', 'state' => 'Punjab'],
            ['name' => 'Hundal', 'state' => 'Punjab'],
            ['name' => 'Jhang', 'state' => 'Punjab'],
            ['name' => 'Jhelum', 'state' => 'Punjab'],
            ['name' => 'Kahna Nau', 'state' => 'Punjab'],
            ['name' => 'Kasur', 'state' => 'Punjab'],
            ['name' => 'Kotli Loharan', 'state' => 'Punjab'],
            ['name' => 'Lahore', 'state' => 'Punjab'],
            ['name' => 'Lodhran', 'state' => 'Punjab'],
            ['name' => 'Mandi Bahauddin', 'state' => 'Punjab'],
            ['name' => 'Multan', 'state' => 'Punjab'],
            ['name' => 'Pattoki', 'state' => 'Punjab'],
            ['name' => 'Qadirpur Ran', 'state' => 'Punjab'],
            ['name' => 'Rahim Yar Khan', 'state' => 'Punjab'],
            ['name' => 'Raiwind', 'state' => 'Punjab'],
            ['name' => 'Rawalpindi', 'state' => 'Punjab'],
            ['name' => 'Rojhan', 'state' => 'Punjab'],
            ['name' => 'Sialkot', 'state' => 'Punjab'],

            // Sindh
            ['name' => 'Hyderabad', 'state' => 'Sindh'],
            ['name' => 'Karachi', 'state' => 'Sindh'],
            ['name' => "Khairpur Mir's", 'state' => 'Sindh'],
            ['name' => 'Malir Cantonment', 'state' => 'Sindh'],
            ['name' => 'Mirpur Khas', 'state' => 'Sindh'],
            ['name' => 'Sukkur', 'state' => 'Sindh'],

            // Khyber Pakhtunkhwa
            ['name' => 'Abbottabad', 'state' => 'Khyber Pakhtunkhwa'],
            ['name' => 'Haripur', 'state' => 'Khyber Pakhtunkhwa'],
            ['name' => 'Malam Jabba', 'state' => 'Khyber Pakhtunkhwa'],
            ['name' => 'Peshawar', 'state' => 'Khyber Pakhtunkhwa'],

            // Balochistan
            ['name' => 'Kot Malik Barkhurdar', 'state' => 'Balochistan'],
            ['name' => 'Quetta', 'state' => 'Balochistan'],

            // Gilgit-Baltistan
            ['name' => 'Skardu', 'state' => 'Gilgit-Baltistan'],

            // Islamabad
            ['name' => 'Islamabad', 'state' => 'Islamabad'],
        ];

        foreach ($cities as $city) {
            DB::table('cities')->updateOrInsert(
                [
                    'name' => $city['name'],
                    'state' => $city['state'],
                ],
                [
                    'aqi' => null,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
