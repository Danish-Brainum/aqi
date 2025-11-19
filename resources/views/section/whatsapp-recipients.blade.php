<section id="whatsapp-recipients" class="tab-content hidden">
    {{-- Top row: CSV Upload + Add Button + Tips --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 mt-4">
        {{-- Tips (order first on small, third on large) --}}
        <div class="order-1 lg:order-3 rounded-2xl border border-slate-200 bg-gradient-to-br from-indigo-600 to-sky-500 p-6 text-white shadow-sm">
            <h3 class="text-base font-semibold">Tips</h3>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-indigo-50/90">
                <li>CSV format: Phone (required), Name (optional)</li>
                <li>Example: <code class="bg-white/20 px-1 rounded">03073017101,John Doe</code></li>
                <li>Only active recipients will receive messages</li>
            </ul>
        </div>
      
        {{-- CSV Upload --}}
        <div class="order-2 lg:order-1 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-center">
            <form action="{{ route('whatsapp-recipients.upload-csv') }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="upload-recipients-form">
                @csrf
                <div id="recipients-dropzone" class="flex flex-col sm:flex-row items-center justify-center gap-4 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-8 py-6 text-slate-500 text-center transition hover:border-indigo-300 hover:bg-indigo-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4" />
                    </svg>
                    <span class="text-center sm:text-left">Drag & drop CSV here or</span>
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700">
                        <input type="file" name="csv" accept=".csv,.txt,.xlsx,.xls" class="hidden" id="recipients-file-input" required>
                        Browse
                    </label>
                </div>
                <div id="recipients-file-error" class="mt-2 text-sm text-red-600"></div>
                <div id="recipients-file-name" class="text-sm text-slate-500"></div>
            </form>
        </div>
      
        {{-- Manual Add CTA --}}
        <div class="order-3 lg:order-2 rounded-2xl border border-slate-200 bg-white px-6 py-12 shadow-sm flex items-center justify-center">
            <button id="open-add-recipient-modal"
                class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 transition">
                + Add Recipient
            </button>
        </div>
    </div>

    {{-- Recipients Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Recipients List</h3>
            <div class="text-sm text-slate-600">
                Total: <span id="recipients-total-count" class="font-semibold">{{ $totalCount ?? $recipients->total() }}</span> | 
                Active: <span id="recipients-active-count" class="font-semibold text-green-600">{{ $activeCount ?? 0 }}</span>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <div class="max-h-96 overflow-y-auto">
                <table id="recipients-table" class="min-w-full divide-y divide-slate-200" data-base-url="{{ url('/whatsapp-recipients') }}" data-start-index="1">
                    <thead class="bg-slate-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-600 w-16">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-600 min-w-[150px]">Phone</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-600 min-w-[200px]">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-600 w-24">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-600 w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white" id="recipients-tbody">
                        @forelse($recipients ?? [] as $recipient)
                            <tr 
                                data-recipient-id="{{ $recipient->id }}"
                                data-active="{{ $recipient->active ? 1 : 0 }}"
                                data-phone="{{ $recipient->phone }}"
                                data-name="{{ $recipient->name }}"
                                data-display-index="{{ $loop->iteration }}"
                                class="recipient-row">
                                <td class="px-4 py-3 text-sm text-slate-700 font-medium recipient-index-cell">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 text-sm text-slate-800 font-mono">{{ $recipient->phone }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ $recipient->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <button 
                                        class="toggle-active-btn inline-flex items-center rounded-full px-3 py-1 text-xs font-medium transition-colors
                                            {{ $recipient->active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}"
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

{{-- Modals --}}
@include('modal.edit-recipient')
@include('modal.whatsapp-recipient-add')

{{-- Overlay --}}
<div id="recipients-overlay" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50">
    <div class="flex h-full w-full items-center justify-center">
        <div class="rounded-2xl bg-white px-8 py-6 shadow-2xl flex flex-col items-center gap-3 text-slate-700">
            <svg class="h-8 w-8 text-indigo-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p id="recipients-overlay-text" class="text-sm font-medium">Processing...</p>
        </div>
    </div>
</div>

