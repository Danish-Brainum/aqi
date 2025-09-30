{{-- Send Modal --}}
<div id="send-modal"
  class="fixed inset-0 z-50 hidden flex  items-center justify-center bg-black/50">
  <div id="sendModalContent"
    class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-lg transform transition-all duration-300 opacity-0 scale-95">
    
    {{-- Header --}}
    <div class="flex items-center justify-between border-b pb-3">
      <h2 class="text-lg font-semibold text-slate-900">Send Message</h2>
      <button id="closeSendModal" class="text-slate-500 hover:text-slate-700">✕</button>
    </div>

    {{-- Form --}}
    <form method="POST" action="" class="mt-4 space-y-4">
      @csrf        
        {{-- City --}}
        <div>
          <label for="sendCity" class="block text-sm font-medium text-slate-700">City</label>
          <select name="city" id="sendCity"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">-- Select City --</option>
            @foreach($results->pluck('city')->unique() as $city)
              <option value="{{ $city }}">{{ $city }}</option>
            @endforeach
          </select>
        </div>

        {{-- Phone --}}
        <div>
          <label for="sendPhone" class="block text-sm font-medium text-slate-700">Phone</label>
          <select name="phone" id="sendPhone"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">-- Select Phone --</option>
            @foreach($results->pluck('phone')->unique() as $phone)
              <option value="{{ $phone }}">{{ $phone }}</option>
            @endforeach
          </select>
        </div>

      {{-- Submit --}}
        <div class="flex justify-end items-center gap-3 mt-6">
        <button type="button" class="px-4 py-2 bg-gray-300 rounded" id="cancelBtn">Cancel</button>

        <button type="submit"
          class="rounded-lg bg-blue-600 px-4 py-2 text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
          Send
        </button>
      </div>
    </form>
  </div>
</div>
