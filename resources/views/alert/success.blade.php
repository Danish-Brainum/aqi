<div id="success-alert" class="relative mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
      <div class="pr-8">
          {{ session('success') }}
      </div>

      <!-- Close Button -->
      <button 
          type="button"
          onclick="document.getElementById('success-alert').style.display='none'"
          class="absolute right-2 top-2 rounded-full p-1 text-green-700 hover:bg-green-100 focus:outline-none"
      >
          <!-- Heroicon X Mark -->
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
      </button>
    </div>