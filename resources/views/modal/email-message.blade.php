<div id="emailMessageModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center">
    <!-- Animated Modal Content -->
    <div id="emailModalContent" class="bg-white rounded-lg w-[36rem] transform opacity-0 scale-95 transition-all duration-300 ease-out shadow-lg">
      
      <!-- Header -->
      <div class="flex items-center justify-between border-b px-6 py-3">
        <h2 class="text-lg font-semibold text-slate-800">Custom Email Messages</h2>
        <button type="button" id="closeEmailMessageModal" class="text-slate-500 hover:text-slate-700">
          <!-- Heroicon X -->
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
  
      <!-- Body -->
      <div class="p-6">
        <form method="POST" action="{{ route('save_messages') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @csrf
            <input type="hidden" name="type" value="email">

            <input type="text" 
            name="good" 
            placeholder="Good (0-50)" 
            value="{{ old('good', $email_messages['good'] ?? '') }}"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200">
     
     <input type="text" 
            name="moderate" 
            placeholder="Moderate (51-100)" 
            value="{{ old('moderate', $email_messages['moderate'] ?? '') }}"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200">
     
     <input type="text" 
            name="unhealthy_sensitive" 
            placeholder="Unhealthy Sensitive (101-150)" 
            value="{{ old('unhealthy_sensitive', $email_messages['unhealthy_sensitive'] ?? '') }}"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200">
     
     <input type="text" 
            name="unhealthy" 
            placeholder="Unhealthy (151-200)" 
            value="{{ old('unhealthy', $email_messages['unhealthy'] ?? '') }}"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200">
     
     <input type="text" 
            name="very_unhealthy" 
            placeholder="Very Unhealthy (201-300)" 
            value="{{ old('very_unhealthy', $email_messages['very_unhealthy'] ?? '') }}"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200">
     
     <input type="text" 
            name="hazardous" 
            placeholder="Hazardous (301+)" 
            value="{{ old('hazardous', $email_messages['hazardous'] ?? '') }}"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200">
     
            <div class="md:col-span-2 flex justify-end gap-3 mt-4">
              <button type="button" id="closeEmailMessageBtn" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
              <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-white shadow hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                Save Messages
              </button>
            </div>
          </form>
      </div>
    </div>
  </div>
  