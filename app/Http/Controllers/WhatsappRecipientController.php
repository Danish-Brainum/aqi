<?php

namespace App\Http\Controllers;

use App\Models\WhatsappRecipient;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

class WhatsappRecipientController extends Controller
{
    /**
     * Display a listing of WhatsApp recipients
     */
    public function index()
    {
        $recipients = WhatsappRecipient::orderBy('created_at', 'desc')->paginate(20);
        
        // Get counts for display
        $totalCount = WhatsappRecipient::count();
        $activeCount = WhatsappRecipient::where('active', true)->count();
        
        return view('section.whatsapp-recipients', compact('recipients', 'totalCount', 'activeCount'));
    }

    /**
     * Store a newly created recipient manually
     */
    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20|unique:whatsapp_recipients,phone',
            'name' => 'nullable|string|max:255',
        ]);

        try {
            $recipient = WhatsappRecipient::create([
                'phone' => $request->phone,
                'name' => $request->name,
                'active' => $request->has('active') ? true : false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Recipient added successfully!',
                'recipient' => $recipient
            ]);
        } catch (Exception $e) {
            Log::error('Error creating recipient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add recipient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified recipient
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'phone' => 'required|string|max:20|unique:whatsapp_recipients,phone,' . $id,
            'name' => 'nullable|string|max:255',
        ]);

        try {
            $recipient = WhatsappRecipient::findOrFail($id);
            $recipient->update([
                'phone' => $request->phone,
                'name' => $request->name,
                'active' => $request->has('active') ? true : false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Recipient updated successfully!',
                'recipient' => $recipient
            ]);
        } catch (Exception $e) {
            Log::error('Error updating recipient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update recipient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified recipient
     */
    public function destroy($id)
    {
        try {
            $recipient = WhatsappRecipient::findOrFail($id);
            $recipient->delete();

            return response()->json([
                'success' => true,
                'message' => 'Recipient deleted successfully!'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting recipient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete recipient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        try {
            $recipient = WhatsappRecipient::findOrFail($id);
            $recipient->active = !$recipient->active;
            $recipient->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!',
                'active' => $recipient->active
            ]);
        } catch (Exception $e) {
            Log::error('Error toggling recipient status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload CSV/Excel file with recipients
     */
    public function uploadCsv(Request $request)
    {
        $request->validate([
            'csv' => 'required|mimes:csv,txt,xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('csv');
            $extension = $file->getClientOriginalExtension();

            // Handle CSV files
            if (in_array($extension, ['csv', 'txt'])) {
                $csv = Reader::createFromPath($file->getRealPath(), 'r');
                $csv->setHeaderOffset(0);

                // Validate headers
                $headers = array_map('strtolower', (array) $csv->getHeader());
                $required = ['phone'];
                $missing = array_values(array_diff($required, $headers));
                
                if (!empty($missing)) {
                    return back()->with('error', 'Invalid CSV headers. Required: Phone (optional: Name)');
                }

                $records = $csv->getRecords();
                $imported = 0;
                $skipped = 0;
                $errors = [];

                foreach ($records as $i => $record) {
                    $normalized = array_change_key_case($record, CASE_LOWER);
                    $phone = trim($normalized['phone'] ?? '');
                    $name = trim($normalized['name'] ?? '');

                    if (empty($phone)) {
                        $skipped++;
                        continue;
                    }

                    // Clean phone number (remove spaces, dashes, etc.)
                    $phone = preg_replace('/[^0-9]/', '', $phone);

                    try {
                        WhatsappRecipient::updateOrCreate(
                            ['phone' => $phone],
                            [
                                'name' => $name ?: null,
                                'active' => true,
                            ]
                        );
                        $imported++;
                    } catch (Exception $e) {
                        $errors[] = "Row " . ($i + 2) . ": " . $e->getMessage();
                        $skipped++;
                    }
                }

                $message = "Successfully imported {$imported} recipient(s)";
                if ($skipped > 0) {
                    $message .= ", skipped {$skipped} duplicate(s) or invalid row(s)";
                }

                return back()->with('success', $message);
            } else {
                // Handle Excel files (xlsx, xls)
                return back()->with('error', 'Excel file support coming soon. Please use CSV format.');
            }
        } catch (Exception $e) {
            Log::error('Error uploading recipients CSV: ' . $e->getMessage());
            return back()->with('error', 'Failed to process file: ' . $e->getMessage());
        }
    }

    /**
     * Get recipient data for editing
     */
    public function show($id)
    {
        try {
            $recipient = WhatsappRecipient::findOrFail($id);
            return response()->json([
                'success' => true,
                'recipient' => $recipient
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient not found'
            ], 404);
        }
    }
}
