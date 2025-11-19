<div id="addRecipientModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div id="addRecipientModalContent" class="bg-white rounded-2xl w-full max-w-lg transform opacity-0 scale-95 transition-all duration-300 ease-out shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Add WhatsApp Recipient</h2>
                <p class="text-xs text-slate-500">Phone number must include country code (e.g., 923001234567)</p>
            </div>
            <button type="button" id="closeAddRecipientModal" class="text-slate-400 hover:text-slate-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="add-recipient-form" class="px-6 py-6 space-y-5">
            @csrf
            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone Number *</label>
                <input type="text" id="phone" name="phone" required
                       class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="923073017101 (without + sign)">
                <p class="mt-1 text-xs text-slate-500">Enter phone number in international format</p>
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

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 transition">
                    Save Recipient
                </button>
                <button type="button" id="cancelAddRecipient" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

