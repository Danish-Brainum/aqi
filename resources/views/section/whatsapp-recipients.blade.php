<section id="whatsapp-recipients" class="tab-content hidden">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800 mb-2">WhatsApp Recipients Management</h2>
        <p class="text-slate-600">Manage phone numbers that will receive WhatsApp messages</p>
    </div>

    {{-- Top Actions: Upload CSV + Add Manual --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- CSV Upload Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Upload CSV/Excel</h3>
            <form action="{{ route('whatsapp-recipients.upload-csv') }}" method="POST" enctype="multipart/form-data" id="upload-recipients-form">
                @csrf
                <div id="recipients-dropzone" class="flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-slate-500 transition hover:border-indigo-300 hover:bg-indigo-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4" />
                    </svg>
                    <span class="text-center">Drag & drop CSV here or</span>
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700">
                        <input type="file" name="csv" accept=".csv,.txt,.xlsx,.xls" class="hidden" id="recipients-file-input" required>
                        Browse File
                    </label>
                </div>
                <div id="recipients-file-error" class="mt-2 text-sm text-red-600"></div>
                <div id="recipients-file-name" class="mt-2 text-sm text-slate-500"></div>
                <div class="mt-4">
                    <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700">
                        Upload Recipients
                    </button>
                </div>
                <div class="mt-3 text-xs text-slate-500">
                    <p><strong>CSV Format:</strong> Phone (required), Name (optional)</p>
                    <p class="mt-1">Example: <code>923073017101,John Doe</code></p>
                </div>
            </form>
        </div>

        {{-- Manual Add Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Add Recipient Manually</h3>
            <form id="add-recipient-form" class="space-y-4">
                @csrf
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone Number *</label>
                    <input type="text" id="phone" name="phone" required
                           class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="923073017101 (without + sign)">
                    <p class="mt-1 text-xs text-slate-500">Enter phone number in international format (e.g., 923073017101)</p>
                </div>
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Name (Optional)</label>
                    <input type="text" id="name" name="name"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="John Doe">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="active" name="active" checked
                           class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="active" class="ml-2 text-sm text-slate-700">Active (will receive messages)</label>
                </div>
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700">
                    Add Recipient
                </button>
            </form>
        </div>
    </div>

    {{-- Recipients Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Recipients List</h3>
            <div class="text-sm text-slate-600">
                Total: <span class="font-semibold">{{ $totalCount ?? $recipients->total() }}</span> | 
                Active: <span class="font-semibold text-green-600">{{ $activeCount ?? 0 }}</span>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <div class="max-h-96 overflow-y-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-600 w-16">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-600 min-w-[150px]">Phone</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-600 min-w-[200px]">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-600 w-24">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-600 w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white" id="recipients-tbody">
                        @forelse($recipients ?? [] as $recipient)
                            <tr data-id="{{ $recipient->id }}" class="hover:bg-indigo-50/40 transition-colors">
                                <td class="px-4 py-3 text-sm text-slate-700 font-medium">{{ $recipient->id }}</td>
                                <td class="px-4 py-3 text-sm text-slate-800 font-mono">{{ $recipient->phone }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ $recipient->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <button 
                                        class="toggle-active-btn inline-flex items-center rounded-full px-3 py-1 text-xs font-medium transition-colors
                                            {{ $recipient->active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                        data-id="{{ $recipient->id }}"
                                        data-url="{{ route('whatsapp-recipients.toggle-active', $recipient->id) }}">
                                        {{ $recipient->active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="inline-flex items-center gap-2">
                                        <button 
                                            class="edit-recipient-btn inline-flex items-center justify-center rounded-md bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200 transition-colors"
                                            data-id="{{ $recipient->id }}">
                                            Edit
                                        </button>
                                        <button 
                                            class="delete-recipient-btn inline-flex items-center justify-center rounded-md bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200 transition-colors"
                                            data-id="{{ $recipient->id }}"
                                            data-url="{{ route('whatsapp-recipients.destroy', $recipient->id) }}">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-base font-semibold text-slate-500">
                                    No recipients found. Add your first recipient above!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if(isset($recipients) && $recipients->hasPages())
            <div class="mt-4">
                {{ $recipients->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</section>

{{-- Edit Modal --}}
@include('modal.edit-recipient')

