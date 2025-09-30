<?php

namespace App\Http\Controllers;

use App\Jobs\FetchAqiJob;
use App\Models\Aqi;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Writer;

class AQIController extends Controller
{
    public function index(Request $request)
    {
        $results = session('aqi_results', []);
    
        // paginate results (uploaded)
        $perPage = 10;
        $page = Paginator::resolveCurrentPage('page');
        $paginatedResults = new LengthAwarePaginator(
            collect($results)->forPage($page, $perPage),
            count($results),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
    
        // paginate deleted_results
        $deleted = session('deleted_results', []);
        $deletedPage = Paginator::resolveCurrentPage('deleted_page');
        $paginatedDeleted = new LengthAwarePaginator(
            collect($deleted)->forPage($deletedPage, $perPage),
            count($deleted),
            $perPage,
            $deletedPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'deleted_page']
        );
    
        // ✅ get cities for Analytics tab
        $cities = City::select('id', 'name', 'aqi', 'status')->get();
    
        return view('dashboard', [
            'results' => $paginatedResults,
            'deleted_results' => $paginatedDeleted,
            'cities' => $cities,   // ✅ pass cities here
        ]);
    }
    

    public function status()
    {
        // return raw JSON instead of view
        return response()->json(
            City::select('id', 'name', 'email', 'state', 'aqi', 'status')->get()
        );
    }


    public function upload(Request $request)
    {
        // 1. If request has CSV, process CSV
        if ($request->hasFile('csv')) {
            $request->validate([
                'csv' => 'required|mimes:csv,txt',
            ]);
    
            try {
                $csv = Reader::createFromPath($request->file('csv')->getRealPath(), 'r');
                $csv->setHeaderOffset(0);
    
                // Validate headers
                $headers = array_map('strtolower', (array) $csv->getHeader());
                $required = ['name', 'email', 'city', 'phone'];
                $missing = array_values(array_diff($required, $headers));
                if (!empty($missing)) {
                    $details = 'Missing required header(s): ' . implode(', ', $missing) . '. Expected headers: Name, Email, City, Phone';
                    return back()->with('error', 'Invalid CSV headers.')->with('error_details', $details);
                }
    
                $records = $csv->getRecords();
                $output = [];
                foreach ($records as $i => $record) {
                    $normalized = array_change_key_case($record, CASE_LOWER);
                    $output[] = $this->processRecord(
                        $normalized['id'] ?? $i,
                        $normalized['name'] ?? '',
                        $normalized['email'] ?? '',
                        $normalized['city'] ?? '',
                        $normalized['phone'] ?? ''
                    );
                    $output;
                }
    
                if (empty($output)) {
                    return back()->with('error', 'The CSV appears to be empty.')
                                 ->with('error_details', 'Add at least one row under the headers: Name, Email, City, Phone');
                }
    
                session(['aqi_results' => $output]);
                return back()->with('results', $output)->with('success', 'CSV processed successfully.');
            } catch (\Throwable $e) {
                return back()->with('error', 'Could not process the CSV file.')
                             ->with('error_details', $e->getMessage());
            }
        }
    
        // 2. If manual input is present (no CSV)
        if ($request->filled(['name', 'email', 'city', 'phone'])) {
            $name  = $request->input('name');
            $email  = $request->input('email');
            $city  = $request->input('city');
            $phone = $request->input('phone');
        
            // get existing results
            $results = session('aqi_results', []);
        
            // calculate next id
            $nextId = count($results) + 1;
        
            // pass id into processRecord
            $record = $this->processRecord($nextId, $name, $email, $city, $phone);
        
            $results[] = $record;
            session(['aqi_results' => $results]);
        
            return back()->with('results', $results)->with('success', 'Record added successfully.');
        }
    
        // 3. If neither provided
        return back()->with('error', 'Please upload a CSV file or provide Name, Email, City, and Phone.');
    }
    
    /**
     * Handle API call + validation for a single record
     */

    private function processRecord($id, $name, $email, $city, $phone)
    {
        $name = trim((string) $name);
        $email = trim((string) $email);
        $city = trim((string) $city);
        $phone = trim((string) $phone);
    
        if ($name === '' || $email === '' || $city === '' || $phone === '') {
            return [
                'id'    => $id,
                'name'    => $name,
                'email'    => $email,
                'city'    => $city,
                'phone'   => $phone,
                'aqi'     => null,
                'message' => "Missing value(s). Each row must include Name, Email, City, and Phone.",
            ];
        }
    
        try {
        //     $response = Http::get("https://api.airvisual.com/v2/city", [
        //         'city'    => $city,
        //         'state'   => $this->getStateByCity($city),
        //         'country' => 'Pakistan',
        //         'key'     => env('IQAIR_API_KEY'),
        //     ]);
    
        //     if ($response->successful() && $response->json('status') === 'success') {
        //         $aqi = $response->json('data.current.pollution.aqius');
        //         $message = $this->getMessage($aqi, $name, $city);
        //     } else {
        //         $aqi = null;
        //         $message = "City not found or API error.";
        //     }
        $aqi = City::where('name', $city)->pluck("aqi")->first();
        $message = $this->getMessage($aqi, $name, $city);
    } catch (\Throwable $e) {
            $aqi = null;
            $message = "Could not fetch AQI (" . $e->getMessage() . ").";
            Log::info("Could not fetch AQI (" . $e->getMessage() . ")");
        }
    
        return [
            'id'    => $id,
            'name'    => $name,
            'email'    => $email,
            'city'    => $city,
            'phone'   => $phone,
            'aqi'     => $aqi,
            'message' => $message,
        ];
    }

    private function getMessage($aqi, $name, $city)
    {
        if (is_null($aqi)) {
            return "Hi {$name}, we couldn’t retrieve air quality data for {$city}.";
        }

        // Load custom messages from DB
        $messages = Aqi::pluck('message','range')->toArray();
        // dd($messages);

        if ($aqi <= 50) {
            return "Hi {$name}! Today AQI in {$city} is {$aqi}. " . ($messages['good'] ?? "the air quality in {$city} is Good 😊 (AQI: {$aqi}). Enjoy your day!");
        } elseif ($aqi <= 100) {
            return "Hi {$name}! Today AQI in {$city} is {$aqi}. " . ($messages['moderate'] ?? "the air quality in {$city} is Moderate 🙂 (AQI: {$aqi}). It’s generally okay.");
        } elseif ($aqi <= 150) {
            return "Hi {$name}! Today AQI in {$city} is {$aqi}. " . ($messages['unhealthy_sensitive'] ?? "the air quality in {$city} is Unhealthy for Sensitive Groups 😷 (AQI: {$aqi}). Be careful if you have breathing issues.");
        } elseif ($aqi <= 200) {
            return "Hi {$name}! Today AQI in {$city} is {$aqi}. " . ($messages['unhealthy'] ?? "the air quality in {$city} is Unhealthy ❌ (AQI: {$aqi}). Try to limit outdoor activity.");
        } elseif ($aqi <= 300) {
            return "Hi {$name}! Today AQI in {$city} is {$aqi}. " . ($messages['very_unhealthy'] ?? "the air quality in {$city} is Very Unhealthy ⚠️ (AQI: {$aqi}). Consider staying indoors.");
        } else {
            return "Hi {$name}! Today AQI in {$city} is {$aqi}. " . ($messages['hazardous'] ?? "the air quality in {$city} is Hazardous ☠️ (AQI: {$aqi}). Stay safe and avoid going outside.");
        }
    }

    public function download()
    {
        $output = session('aqi_results', []);

        if (empty($output)) {
            return redirect()->route('home')->with('error', 'No results available to download.');
        }

        $csv = Writer::createFromString('');
        $csv->insertOne(['id','name', 'email', 'city','phone','aqi', 'message']);
        $csv->insertAll($output);

        return response((string) $csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="aqi_results.csv"');
    }

    public function saveMessages(Request $request)
    {
        $data = $request->only([
            'good',
            'moderate',
            'unhealthy_sensitive',
            'unhealthy',
            'very_unhealthy',
            'hazardous',
        ]);

        foreach ($data as $range => $message) {
            if (!empty($message)) {
                Aqi::updateOrCreate(
                    ['range' => $range],
                    ['message' => $message]
                );
            }
        }

        return back()->with('success', 'Custom messages saved successfully!');
    }
    public function update(Request $request)
    {
        $results = session('aqi_results', []);
        $index = $request->input('index'); // row index
    
        if (isset($results[$index])) {
            $results[$index]['name'] = $request->input('name');
            $results[$index]['email'] = $request->input('email');
            $results[$index]['phone'] = $request->input('phone');
            $results[$index]['message'] = $request->input('message');
            session(['aqi_results' => $results]);
        }
    
        return response()->json(['success' => true]);
    }
    
    public function delete(Request $request)
    {
        $id = $request->input('id');
        $results = session('aqi_results', []);
        $deleted = session('deleted_results', []);
    
        foreach ($results as $key => $row) {
            if (($row['id'] ?? null) == $id) {   // loose comparison
                // PREPEND the deleted item so newest deleted appear first
                array_unshift($deleted, $row);
    
                // remove from active results
                unset($results[$key]);
    
                session(['aqi_results' => array_values($results)]);
                session(['deleted_results' => $deleted]);
    
                // return single-row HTML (used when you want to append a row — we won't rely on this,
                // but keep it for compatibility)
                $rowHtml = view('partials.deleted-rows', ['row' => $row])->render();
    
                return response()->json(['success' => true, 'row' => $rowHtml]);
            }
        }
    
        return response()->json(['success' => false]);
    }
    public function deletedTable(Request $request)
    {
        $deleted_results = session('deleted_results', []);
    
        $perPage = 10;
        $page = Paginator::resolveCurrentPage('deleted_page');
    
        $deleted_results = new LengthAwarePaginator(
            collect($deleted_results)->forPage($page, $perPage),
            count($deleted_results),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'deleted_page']
        );
    
        // Return only partial HTML (AJAX target)
        return view('partials.deleted-table', compact('deleted_results'));
    }
    public function fetchAll()
    {
        $cities = \App\Models\City::all();
        $delaySeconds = 0;
    
        foreach ($cities as $city) {
            // Immediately mark as processing so UI shows spinner right away
            $city->update(['status' => 'processing']);
    
            dispatch(new \App\Jobs\FetchAqiJob($city->name, $city->state))
                ->delay(now()->addSeconds($delaySeconds));
    
            $delaySeconds += 12; // space each job by 12 seconds
        }
    
        return response()->json(['success' => 'AQI jobs dispatched successfully with 12s spacing.']);
    }
    
}
