<?php

namespace App\Http\Controllers;

use App\Jobs\FetchAqiJob;
use App\Jobs\SendWhatsappMessageJob;
use App\Mail\AutoReportMail;
use App\Models\AQI;
use App\Models\City;
use App\Models\CSV;
use App\Models\Settings;
use App\Models\WhatsappRecipient;
use App\Services\AqiFetchService;
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
        $settings = Settings::first();

        return view('dashboard', [
            'results' => $paginatedResults,
            'deleted_results' => $paginatedDeleted,
            'cities' => $cities,
            'email_messages' => $email_messages,
            'whatsapp_messages' => $whatsapp_messages,
            'settings' => $settings
        ]);
    }



    public function status()
    {
        // return raw JSON - no auto-fix needed since we now ensure AQI and status are set together
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
            return "Hi {$name}, we couldn't retrieve air quality data for {$city}.";
        }
        
        // Load custom messages from DB (only message body, header and footer are added in email template)
        $messages = Aqi::where('type', 'email')
            ->whereNull('city') // Get global messages
            ->pluck('message', 'range')
            ->toArray();

        // Determine AQI range
        $range = null;
        if ($aqi <= 50) {
            $range = 'good';
        } elseif ($aqi <= 100) {
            $range = 'moderate';
        } elseif ($aqi <= 150) {
            $range = 'unhealthy_sensitive';
        } elseif ($aqi <= 200) {
            $range = 'unhealthy';
        } elseif ($aqi <= 300) {
            $range = 'very_unhealthy';
        } else {
            $range = 'hazardous';
        }

        // Get message body from database, or use fallback
        $messageBody = $messages[$range] ?? null;
        
        if ($messageBody) {
            // Return the message body as-is (header and footer are added in email template)
            return "The air quality index in {$city} is {$aqi}, {$messageBody}";
        }

        // Fallback messages if database doesn't have them
        $fallbackMessages = [
            'good' => "Today's air is fresh and safe. A great day to enjoy the outdoors!\n\nLet's keep it that way — choose public transport, plant trees, and protect clean air.",
            'moderate' => "Air quality is acceptable, but may affect sensitive individuals.\n\nIf you feel discomfort, take it easy and stay hydrated.\n\nLet's reduce car use and support cleaner choices.",
            'unhealthy_sensitive' => "Today's air may cause coughing or irritation for children and elders.\n\nLimit outdoor play, wear a mask if needed, and keep windows closed.\n\nLet's care for our loved ones together.",
            'unhealthy' => "Air quality is poor today. Everyone may feel its effects.\n\nStay indoors when possible, use air purifiers, and avoid traffic-heavy areas.\n\nLet's protect our lungs and help others do the same.",
            'very_unhealthy' => "Breathing this air can be harmful. Let's take extra care today.\n\nSeal windows, avoid outdoor exposure, and check on vulnerable family members.\n\nTogether, we can breathe safer.",
            'hazardous' => "This is an air emergency. Everyone is at risk.\n\nStay indoors, avoid all outdoor activity, and follow safety alerts.\n\nLet's protect our breath, our health, and each other.",
        ];

        return $fallbackMessages[$range] ?? "";
    }

    /**
     * Get WhatsApp message for a specific city and AQI range
     */
    private function getWhatsappMessage($aqi, $cityName)
    {
        // Check if AQI is null, empty, or 'Error'
        if (is_null($aqi) || $aqi === 'Error' || $aqi === '') {
            return null;
        }

        // Ensure AQI is numeric for comparison
        $aqi = is_numeric($aqi) ? (int) $aqi : null;

        if (is_null($aqi)) {
            return null;
        }

        // Determine the range based on AQI
        $range = null;
        if ($aqi <= 50) {
            $range = 'good';
        } elseif ($aqi <= 100) {
            $range = 'moderate';
        } elseif ($aqi <= 150) {
            $range = 'unhealthy_sensitive';
        } elseif ($aqi <= 200) {
            $range = 'unhealthy';
        } elseif ($aqi <= 300) {
            $range = 'very_unhealthy';
        } else {
            $range = 'hazardous';
        }

        // Try to get city-specific message first
        $message = AQI::where('type', 'whatsapp')
            ->where('city', $cityName)
            ->where('range', $range)
            ->value('message');

        // If no city-specific message, use default
        if (empty($message)) {
            if ($aqi <= 50) {
                $message = "the air quality in {$cityName} is Good 😊 (AQI: {$aqi}). Enjoy your day!";
            } elseif ($aqi <= 100) {
                $message = "the air quality in {$cityName} is Moderate 🙂 (AQI: {$aqi}). It's generally okay.";
            } elseif ($aqi <= 150) {
                $message = "the air quality in {$cityName} is Unhealthy for Sensitive Groups 😷 (AQI: {$aqi}). Be careful if you have breathing issues.";
            } elseif ($aqi <= 200) {
                $message = "the air quality in {$cityName} is Unhealthy ❌ (AQI: {$aqi}). Try to limit outdoor activity.";
            } elseif ($aqi <= 300) {
                $message = "the air quality in {$cityName} is Very Unhealthy ⚠️ (AQI: {$aqi}). Consider staying indoors.";
            } else {
                $message = "the air quality in {$cityName} is Hazardous ☠️ (AQI: {$aqi}). Stay safe and avoid going outside.";
            }
        }

        return $message;
    }

    /**
     * Sanitize message text for WhatsApp template parameters
     * WhatsApp doesn't allow: newlines (\n), tabs (\t), or more than 4 consecutive spaces
     * 
     * @param string $message
     * @return string
     */
    private function sanitizeWhatsAppMessage(string $message): string
    {
        if (empty($message)) {
            return '';
        }

        // Replace newlines and tabs with single space
        $sanitized = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $message);
        
        // Replace multiple consecutive spaces (more than 1) with single space
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        
        // Trim leading and trailing spaces
        $sanitized = trim($sanitized);
        
        // Ensure no more than 4 consecutive spaces (extra safety check)
        $sanitized = preg_replace('/ {5,}/', '    ', $sanitized);
        
        return $sanitized;
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
        $city = $request->input('city'); // city name or null for global

        // Validate that city is provided for WhatsApp messages
        if ($type === 'whatsapp' && empty($city)) {
            return back()->with('error', 'Please select a city to save WhatsApp messages.');
        }

        foreach ($data as $range => $message) {
            if (!empty($message)) {
                // Sanitize WhatsApp messages: remove newlines, tabs, and limit consecutive spaces
                // WhatsApp template parameters cannot contain newlines, tabs, or more than 4 consecutive spaces
                if ($type === 'whatsapp') {
                    $message = $this->sanitizeWhatsAppMessage($message);
                }
                
                AQI::updateOrCreate(
                    [
                        'range' => $range,
                        'type' => $type,
                        'city' => $city
                    ],
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

        $message = ucfirst($type) . ' messages saved successfully';
        if ($city) {
            $message .= ' for ' . $city;
        }
        $message .= ' and updated in the table!';

        return back()
            ->with('results', $results)
            ->with('success', $message);
    }

    public function getCityMessages(Request $request)
    {
        $city = $request->input('city');
        $type = $request->input('type', 'whatsapp');

        if (empty($city)) {
            return response()->json([
                'success' => false,
                'message' => 'City is required'
            ], 400);
        }

        $messages = AQI::where('type', $type)
            ->where('city', $city)
            ->pluck('message', 'range')
            ->toArray();

        return response()->json([
            'success' => true,
            'messages' => [
                'good' => $messages['good'] ?? '',
                'moderate' => $messages['moderate'] ?? '',
                'unhealthy_sensitive' => $messages['unhealthy_sensitive'] ?? '',
                'unhealthy' => $messages['unhealthy'] ?? '',
                'very_unhealthy' => $messages['very_unhealthy'] ?? '',
                'hazardous' => $messages['hazardous'] ?? '',
            ]
        ]);
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
        try {
            $result = AqiFetchService::fetchAllCities();

            if (!$result['success'] || $result['dispatched_count'] === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No cities found to update.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => "✅ Started updating AQI values for {$result['dispatched_count']} cities. The table will update automatically as each city's data is fetched. Estimated completion: {$result['estimated_time']} minutes."
            ]);
        } catch (Exception $e) {
            Log::error("💥 [AQIController] Error in fetchAll: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to queue cities for update. Please try again.'
            ], 500);
        }
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
                    'subject' => 'Mr. Pulmo — Caring for You', // Header as subject
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

        if ($cities->isEmpty()) {
            return back()->with('error', 'No records found to send WhatsApp messages.');
        }

        // Get all active WhatsApp recipients
        $recipients = WhatsappRecipient::getActiveRecipients();
        
        if (empty($recipients)) {
            return back()->with('error', 'No WhatsApp recipients found. Please add recipients first.');
        }

        $totalMessagesQueued = 0;

        foreach ($cities as $row) {
            // Access as object property, not array
            $aqi = $row->aqi;
            $cityName = $row->name;

            // Check if AQI is valid (not null, not 'Error', and numeric)
            if ($aqi !== null && $aqi !== 'Error' && is_numeric($aqi)) {
                $message = $this->getWhatsappMessage($aqi, $cityName);

                if ($message) {
                    // Send to all recipients
                    foreach ($recipients as $phoneNumber) {
                        dispatch(new SendWhatsappMessageJob($phoneNumber, $cityName, $aqi, $message));
                        $totalMessagesQueued++;
                    }
                }
            }
        }

        $recipientCount = count($recipients);
        return back()->with('success', "WhatsApp messages are being sent in background to {$recipientCount} recipient(s). Total {$totalMessagesQueued} message(s) queued.");
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
