<div id="editModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center">
  <!-- Animated Modal Content -->
  <div id="modalContent" class="bg-white rounded-lg w-96 transform opacity-0 scale-95 transition-all duration-300 ease-out shadow-lg">
    
    <!-- Header -->
    <div class="flex items-center justify-between border-b px-6 py-3">
      <h2 class="text-lg font-semibold text-slate-800">Edit Record</h2>
      <button type="button" id="closeModal" class="text-slate-500 hover:text-slate-700">
        <!-- Heroicon X -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Body -->
    <div class="p-6">
      <form id="editForm">
        <input type="hidden" id="editIndex">

        <div>
          <label class="block text-sm font-medium">Name</label>
          <input type="text" id="editName" class="w-full border rounded px-3 py-2 mt-1 text-sm">
        </div>

        <div class="mt-3">
          <label class="block text-sm font-medium">Email</label>
          <input type="text" id="editEmail" class="w-full border rounded px-3 py-2 mt-1 text-sm">
        </div>

        <div class="mt-3">
          <label class="block text-sm font-medium">Phone</label>
          <input type="text" id="editPhone" class="w-full border rounded px-3 py-2 mt-1 text-sm">
        </div>

        <div class="mt-3">
          <label class="block text-sm font-medium">Message</label>
          <textarea id="editMessage" rows="5" class="w-full border rounded px-3 py-2 mt-1 text-sm resize-none"></textarea>
        </div>

        <!-- Footer -->
        <div class="flex justify-end items-center gap-3 mt-6">
          <!-- Loader -->
          <span id="loadingSpinner" class="hidden text-sm text-gray-500 flex items-center gap-1">
            <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            Saving...
          </span>

          <button type="button" class="px-4 py-2 bg-gray-300 rounded" id="cancelBtn">Cancel</button>
          <button 
            type="submit"  
            id="saveEditBtn" 
            class="px-4 py-2 bg-indigo-600 text-white rounded"
            data-url="{{ route('records.update') }}">
            Save
          </button>
        </div>
      </form>
    </div>
  </div>
</div>