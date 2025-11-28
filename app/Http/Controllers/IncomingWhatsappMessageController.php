<?php

namespace App\Http\Controllers;

use App\Models\IncomingWhatsappMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncomingWhatsappMessageController extends Controller
{
    /**
     * Display a listing of incoming messages
     */
    public function index(Request $request)
    {
        $perPage = $request->query('perPage', 20);
        
        $messages = IncomingWhatsappMessage::orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        $totalCount = IncomingWhatsappMessage::count();
        $unreadCount = IncomingWhatsappMessage::where('read', false)->count();
        
        return view('section.incoming-messages', compact('messages', 'totalCount', 'unreadCount'));
    }

    /**
     * Get messages as JSON (for AJAX refresh)
     */
    public function list(Request $request)
    {
        try {
            $perPage = $request->query('perPage', 20);
            
            $messages = IncomingWhatsappMessage::orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            $totalCount = IncomingWhatsappMessage::count();
            $unreadCount = IncomingWhatsappMessage::where('read', false)->count();
            
            return response()->json([
                'success' => true,
                'messages' => $messages->items(),
                'totalCount' => $totalCount,
                'unreadCount' => $unreadCount,
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching incoming messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead($id)
    {
        try {
            $message = IncomingWhatsappMessage::findOrFail($id);
            $message->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'Message marked as read'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking message as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark message as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all messages as read
     */
    public function markAllAsRead()
    {
        try {
            IncomingWhatsappMessage::where('read', false)->update(['read' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'All messages marked as read'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking all messages as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all messages as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a message
     */
    public function destroy($id)
    {
        try {
            $message = IncomingWhatsappMessage::findOrFail($id);
            $message->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete message: ' . $e->getMessage()
            ], 500);
        }
    }
}
