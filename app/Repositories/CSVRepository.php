<?php

namespace App\Repositories;

use App\Models\CSV;

class CSVRepository
{
    public function all(): array
    {
        $results = session('aqi_results', []);

        if (empty($results)) {
            $results = CSV::all()->map(function ($row) {
                return [
                    'id'      => $row->id,
                    'name'    => $row->name,
                    'email'   => $row->email,
                    'city'    => $row->city,
                    'phone'   => $row->phone,
                    'aqi'     => $row->aqi,
                    'message' => $row->message,
                ];
            })->toArray();

            session()->put('aqi_results', $results);
        }

        return $results;
    }
}
