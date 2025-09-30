<div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <div class="font-semibold">{{ session('error') }}</div>
        @if (session('error_details'))
          <div class="mt-1 text-red-700/90">{{ session('error_details') }}</div>
        @endif
      </div>