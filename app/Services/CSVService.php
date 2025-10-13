<?php

namespace App\Services;

use App\Repositories\AQIRepository;
use App\Repositories\CityRepository;
use App\Repositories\CSVRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

class CSVService
{
    protected $aqi_repository;
    protected $city_repository;
    protected $csv_repository;

    public function __construct(AQIRepository $aqi_repository, CityRepository $city_repository, CSVRepository $csv_repository)
    {
        $this->aqi_repository = $aqi_repository;
        $this->city_repository = $city_repository;
        $this->csv_repository = $csv_repository;
    }

    public function index(Request $request): array
    {
        $paginatedResults = getPaginatedResults($request->query('perPage',10), session('aqi_results', []), 'results_page');
        $paginatedDeleted = getPaginatedResults($request->query('perPage',10), session('deleted_results', []), 'deleted_results');

        return [
            'results' => $paginatedResults,
            'deleted_results' => $paginatedDeleted,
            'cities' => $this->city_repository->all(),
            'email_messages' => $this->aqi_repository->getEmailMessages(),
            'whatsapp_messages' => $this->aqi_repository->getWhatsappMessages(),
        ];
    }


    public function upload($file)
    {
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
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
            throw new \Exception('The CSV appears to be empty.');
        }

        return $output;
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
            $results = $this->csv_repository->all();
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
        $aqi = $this->city_repository->show($city);
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
        $messages = $this->aqi_repository->getEmailMessages();

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
}
