<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Http\Requests\StoreSettingsRequest;
use App\Http\Requests\UpdateSettingsRequest;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSettingsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Settings $settings)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Settings $settings)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSettingsRequest $request)
    {
        // Get the global settings record
        // Always get the first (and only) settings record
        $settings = Settings::first();

        // Get the time values from request
        $morningTime = $request->input('morning_time');
        $eveningTime = $request->input('evening_time');

        // Ensure times are in HH:MM:SS format for database
        $morningTime = $this->formatTimeForDatabase($morningTime);
        $eveningTime = $this->formatTimeForDatabase($eveningTime);

        // Prepare the data to save
        $data = [
            'morning_time' => $morningTime,
            'evening_time' => $eveningTime,
        ];

        if (!$settings) {
            // If no record exists, create one
            $settings = Settings::create($data);
        } else {
            // If record exists, update it
            $settings->update($data);
        }

        // Refresh to get the latest data
        $settings->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $settings,
        ]);
    }

    /**
     * Format time string to HH:MM:SS format for database
     */
    private function formatTimeForDatabase($time)
    {
        // If time is already in HH:MM:SS format, return as is
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }
        
        // If time is in HH:MM format, add :00 for seconds
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }
        
        // Return as is if format is unexpected
        return $time;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Settings $settings)
    {
        //
    }
}
