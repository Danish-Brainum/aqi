<?php

namespace App\Repositories;

use App\Models\City;

class CityRepository
{
    public function all()
    {
        return City::all();
    }

    public function show(string $city): ?int
    {
        return City::where('name', $city)->pluck('aqi')->first();
    }
}
