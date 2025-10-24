<?php

namespace App\Http\Controllers;

use App\Jobs\FetchAqiJob;
use App\Jobs\SendWhatsappMessageJob;
use App\Mail\AutoReportMail;
use App\Models\AQI;
use App\Models\City;
use App\Models\CSV;
use App\Services\CSVService;
use App\Services\WhatsappService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use League\Csv\Reader;
use League\Csv\Writer;

class AQIController extends Controller
{

    public function index(Request $request)
    {
        // If session has results, use them, else load from DB
        $results = session('aqi_results', []);
    
        if (empty($results)) {
            // ✅ fetch from DB if session is empty
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
        }

        session()->put('aqi_results', $results);
    
        // paginate results
        $perPage = $request->query('perPage', 10);
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
    
        // ✅ get cities for aqi_info tab
        $cities = City::select('id', 'name', 'aqi', 'status')->get();
        $email_messages = AQI::where('type', 'email')->pluck('message', 'range')->toArray();
        $whatsapp_messages = AQI::where('type', 'whatsapp')->pluck('message', 'range')->toArray();
    
        return view('dashboard', [
            'results' => $paginatedResults,
            'deleted_results' => $paginatedDeleted,
            'cities' => $cities,
            'email_messages' => $email_messages,
            'whatsapp_messages' => $whatsapp_messages
        ]);
    }
    
    

    public function status()
    {
        // return raw JSON instead of view
        return response()->json(
            City::select('id', 'name', 'state', 'aqi', 'status')->get()
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
    
        return back()->with('error', 'Please upload a CSV file or provide Name, Email, City, and Phone.');
    }
    
    public function addManualRecord(Request $request) {
        $name  = $request->input('name');
        $email = $request->input('email');
        $city  = $request->input('city');
        $phone = $request->input('phone');

        // ✅ Check session first
        $results = session('aqi_results', []);

        // ✅ If session is empty → fallback to DB
        if (empty($results)) {
            $results = CSV::all()->toArray();
        }

        // ✅ Calculate next ID
        $maxId = 0;
        foreach ($results as $row) {
            $maxId = max($maxId, $row['id']);
        }
        $nextId = $maxId + 1;

        // ✅ Create new record
        $record = $this->processRecord($nextId, $name, $email, $city, $phone);

        // ✅ Append & store in session
        $results[] = $record;
        session(['aqi_results' => $results]);

        return back()->with('results', $results)->with('success', 'Record added successfully.');
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
        $messages = Aqi::where('type', 'email')->pluck('message','range')->toArray();

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
    
        $type = $request->input('type'); // whatsapp or email
    
        foreach ($data as $range => $message) {
            if (!empty($message)) {
                AQI::updateOrCreate(
                    ['range' => $range, 'type' => $type],
                    ['message' => $message]
                );
            }
        }
    
        // ✅ Refresh the existing session results with new messages
        $results = session('aqi_results', []);
        foreach ($results as $key => $row) {
            $results[$key]['message'] = $this->getMessage($row['aqi'], $row['name'], $row['city']);
        }
    
        // ✅ Store the updated data back to session
        session(['aqi_results' => $results]);
    
        return back()
            ->with('results', $results)
            ->with('success', ucfirst($type) . ' messages saved successfully and updated in the table!');
    }
    
    
    public function update(Request $request)
    {
        $id = $request->input('id');

        $results = session('aqi_results', []);
            foreach ($results as &$record) {
                if ($record['id'] == $id) {
                    $record['name']    = $request->input('name');
                    $record['email']   = $request->input('email');
                    $record['phone']   = $request->input('phone');
                    $record['message'] = $request->input('message');
                    break;
                }
            }
            session(['aqi_results' => $results]);
            return response()->json(['success' => true, 'message' => 'Record updated in session']);
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');
        $results = session('aqi_results', []);
        $deleted = session('deleted_results', []);

        // ✅ Case 1: session has results
        if (!empty($results)) {
            foreach ($results as $key => $row) {
                if (($row['id'] ?? null) == $id) {
                    // PREPEND deleted item into deleted_results
                    array_unshift($deleted, $row);
    
                    // remove from active results (but not from DB)
                    unset($results[$key]);
    
                    // update sessions
                    session(['aqi_results' => array_values($results)]);
                    session(['deleted_results' => $deleted]);

                    // return deleted row html
                    $rowHtml = view('partials.deleted-rows', ['row' => $row])->render();

                    return response()->json(['success' => true, 'row' => $rowHtml]);
                }
            }
        }

        // ✅ Case 2: session empty → delete from DB
        $row = CSV::find($id);

        if ($row) {
             // mark this row as deleted in session
            $deletedRow = $row->toArray();
            array_unshift($deleted, $deletedRow);
            session(['deleted_results' => $deleted]);

            $rowHtml = view('partials.deleted-rows', ['row' => $deletedRow])->render();
            return response()->json(['success' => true, 'row' => $rowHtml]);
        }

        return response()->json(['success' => false]);
    }

    public function deletedTable(Request $request)
    {
        $deleted_results = session('deleted_results', []);
        $perPage = $request->query('perPage', 10);
        $page = Paginator::resolveCurrentPage('deleted_page');
    
        $deleted_results = new LengthAwarePaginator(
            collect($deleted_results)->forPage($page, $perPage),
            count($deleted_results),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'deleted_page']
        );
    
        $html = view('partials.deleted-table', compact('deleted_results'))->render();
    
        // ✅ Return JSON with `html` key
        return response()->json(['html' => $html]);
    }
    
    
    public function fetchAll()
    {
        $cities = City::all();
        $delaySeconds = 0;
    
        foreach ($cities as $city) {
            // Immediately mark as processing so UI shows spinner right away
            $city->update(['status' => 'processing']);
    
            dispatch(new FetchAqiJob($city->name, $city->state))
                ->delay(now()->addSeconds($delaySeconds));
    
            $delaySeconds += 12; // space each job by 12 seconds
        }
    
        return response()->json(['success' => 'Cities are updating successfully.']);
    }



    public function sendEmails(Request $request)
    {
        // Try to get results from session
        $results = session('aqi_results', []);

        // If session is empty or null, load from AQI model
        if (empty($results)) {
            $results = AQI::whereNotNull('email')
                ->get(['email', 'message'])
                ->toArray();
        }

        // Prepare and clean valid email entries
        $emails = collect($results)
            ->filter(fn($item) => !empty($item['email']) && !empty($item['message']))
            ->unique('email')
            ->values();

        if ($emails->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No valid emails found.']);
        }

        // Loop and send emails
        foreach ($emails as $email) {
            try {
                $data = [
                    'email' => $email['email'],
                    'message' => $email['message'],
                ];

                Mail::to($email['email'])->send(new AutoReportMail($data));
            } catch (\Exception $e) {
                Log::error("Failed sending email to {$email['email']}: " . $e->getMessage());
            }
        }

        return response()->json(['success' => true, 'count' => $emails->count()]);
    }


    public function sendWhatsapp()
    {
        $cities = City::select('name', 'aqi')->get();
        $message = AQI::where('type', 'whatsapp')->get();

        // dd($cities, $message);
        if ($cities->isEmpty()) {
            return back()->with('error', 'No records found to send WhatsApp messages.');
        }

        foreach ($cities as $row) {
            if ($row['aqi'] != 'Error') {
                $to = "923045039326"; // Or $row['phone'] if exists
                if ($row['aqi'] <= 50) {
                    $message = ($messages['good'] ?? "the air quality in {$row['name']} is Good 😊 (AQI: {$row['aqi']}). Enjoy your day!");
                } elseif ($row['aqi'] <= 100) {
                    $message = ($messages['moderate'] ?? "the air quality in {$row['name']} is Moderate 🙂 (AQI: {$row['aqi']}). It’s generally okay.");
                } elseif ($row['aqi'] <= 150) {
                    $message = ($messages['unhealthy_sensitive'] ?? "the air quality in {$row['name']} is Unhealthy for Sensitive Groups 😷 (AQI: {$row['aqi']}). Be careful if you have breathing issues.");
                } elseif ($row['aqi'] <= 200) {
                    $message = ($messages['unhealthy'] ?? "the air quality in {$row['name']} is Unhealthy ❌ (AQI: {$row['aqi']}). Try to limit outdoor activity.");
                } elseif ($row['aqi'] <= 300) {
                    $message = ($messages['very_unhealthy'] ?? "the air quality in {$row['name']} is Very Unhealthy ⚠️ (AQI: {$row['aqi']}). Consider staying indoors.");
                } else {
                    $message = ($messages['hazardous'] ?? "the air quality in {$row['name']} is Hazardous ☠️ (AQI: {$row['aqi']}). Stay safe and avoid going outside.");
                }
                // dump( $row['name'], $row['aqi'], $message);
                dispatch(new SendWhatsappMessageJob($to, $row['name'], $row['aqi'], $message));
            }
        }

        // dd('fasdfsa');
        return back()->with('success', 'WhatsApp messages are being sent in background.');
    }

    public function saveCSV(Request $request)
    {
        try {
            $results = session('aqi_results', []);
            $deleted = session('deleted_results', []);
    
            // dd($deleted);
            // ✅ Step 1: Delete rows marked as deleted
            if (!empty($deleted)) {
                $idsToDelete = collect($deleted)->pluck('id')->filter()->toArray();
                if (!empty($idsToDelete)) {
                    CSV::whereIn('id', $idsToDelete)->delete();
                    Log::info('Deleted records: ' . implode(',', $idsToDelete));                    
                }
            }
            DB::beginTransaction(); 
    
            Log::info('Upserting CSV records...');
    
          
            CSV::upsert(
                $results,
                ['id'], 
                ['name','email', 'city', 'phone', 'aqi', 'message']
            );
             
    
            DB::commit();
    
            session(['aqi_results' => $results]);
            Log::info('CSV data saved successfully.');
    
            return response()->json([
                'success' => true,
                'message' => 'All results stored successfully!'
            ]);
    
        } catch (Exception $e) {
            DB::rollBack(); // ✅ rollback transaction on error
            Log::error("Error saving results: " . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Error saving results.'
            ]);
        }
    }
}