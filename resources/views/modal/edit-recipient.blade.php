<div id="editRecipientModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg w-[500px] max-w-full transform opacity-0 scale-95 transition-all duration-300 ease-out shadow-lg">
        <div class="flex items-center justify-between p-6 border-b border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800">Edit Recipient</h2>
            <button type="button" id="closeEditRecipientModal" class="text-slate-500 hover:text-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="edit-recipient-form" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-recipient-id" name="id">
            
            <div>
                <label for="edit-phone" class="block text-sm font-medium text-slate-700 mb-1">Phone Number *</label>
                <input type="text" id="edit-phone" name="phone" required
                       class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="923073017101">
            </div>
            
            <div>
                <label for="edit-name" class="block text-sm font-medium text-slate-700 mb-1">Name (Optional)</label>
                <input type="text" id="edit-name" name="name"
                       class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="John Doe">
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="edit-active" name="active"
                       class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label for="edit-active" class="ml-2 text-sm text-slate-700">Active (will receive messages)</label>
            </div>
            
            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700">
                    Update Recipient
                </button>
                <button type="button" id="cancelEditRecipient" class="px-4 py-2 bg-gray-300 rounded-lg text-gray-700 hover:bg-gray-400">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

