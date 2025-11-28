<section id="incoming-messages" class="tab-content hidden">
    {{-- Header with Stats --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Incoming WhatsApp Messages</h3>
            <div class="flex items-center gap-4">
                <div class="text-sm text-slate-600">
                    Total: <span id="messages-total-count" class="font-semibold">{{ $totalCount ?? $messages->total() }}</span> | 
                    Unread: <span id="messages-unread-count" class="font-semibold text-red-600">{{ $unreadCount ?? 0 }}</span>
                </div>
                <button id="mark-all-read-btn" 
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 transition">
                    Mark All as Read
                </button>
                <button id="refresh-messages-btn" 
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow hover:bg-slate-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    {{-- Messages Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">From</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200" id="messages-tbody">
                    @forelse($messages ?? [] as $message)
                        <tr class="hover:bg-slate-50 {{ !$message->read ? 'bg-indigo-50/50' : '' }}" data-message-id="{{ $message->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(!$message->read)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Unread
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        Read
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ $message->from }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $message->type === 'text' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $message->type === 'image' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $message->type === 'video' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $message->type === 'audio' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $message->type === 'document' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $message->type === 'location' ? 'bg-pink-100 text-pink-800' : '' }}">
                                    @if($message->type === 'text') 💬 Text
                                    @elseif($message->type === 'image') 🖼️ Image
                                    @elseif($message->type === 'video') 🎥 Video
                                    @elseif($message->type === 'audio') 🎵 Audio
                                    @elseif($message->type === 'document') 📄 Document
                                    @elseif($message->type === 'location') 📍 Location
                                    @else {{ ucfirst($message->type) }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-900">
                                    @if($message->type === 'text')
                                        {{ Str::limit($message->message, 100) }}
                                    @elseif($message->type === 'location')
                                        📍 Location ({{ number_format($message->latitude, 6) }}, {{ number_format($message->longitude, 6) }})
                                    @elseif($message->message)
                                        {{ Str::limit($message->message, 50) }} (Caption)
                                    @else
                                        <span class="text-slate-400">No caption</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ $message->created_at->format('M d, Y') }}<br>
                                {{ $message->created_at->format('h:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if(!$message->read)
                                    <button onclick="markAsRead({{ $message->id }})" 
                                        class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        Mark as Read
                                    </button>
                                @endif
                                <button onclick="deleteMessage({{ $message->id }})" 
                                    class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                No messages received yet. Messages from users will appear here.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if(isset($messages) && $messages->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $messages->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</section>

<script>
function markAsRead(messageId) {
    fetch(`/incoming-messages/${messageId}/mark-as-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark message as read');
    });
}

function deleteMessage(messageId) {
    if (!confirm('Are you sure you want to delete this message?')) {
        return;
    }
    
    fetch(`/incoming-messages/${messageId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete message');
    });
}

// Refresh messages
document.getElementById('refresh-messages-btn')?.addEventListener('click', function() {
    location.reload();
});

// Mark all as read
document.getElementById('mark-all-read-btn')?.addEventListener('click', function() {
    fetch('/incoming-messages/mark-all-as-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark all messages as read');
    });
});
</script>

