<section id="aqi_info" class="tab-content hidden">
    <div id="alert-container"></div>

    <div class="m-3  flex space-x-3 items-center justify-end">
      <button 
      id="whatsappMessage"
      type="button"
      class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-4 py-2 rounded-lg shadow transition">
      Messages
    </button>
      
    
      {{-- <div class="inline-flex items-center gap-2"> --}}

        <button 
        id="fetchAll" 
        class="border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white px-4 py-2 rounded-lg shadow transition">
        Update
      </button>
        <form method="POST" action="{{ route('sendWhatsapp') }}">
          @csrf
          <button 
            type="submit"
            class="border border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white px-4 py-2 rounded-lg shadow transition">
            Send
          </button>
        </form>
      </div>
    {{-- </div> --}}
    
    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
      <div class="max-h-96 lg:max-h-110 overflow-y-auto">
        <table id="aqi-table" class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">ID</th>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">City</th>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">State</th>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">AQI</th>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Status</th>
            </tr>
          </thead>
          <tbody id="aqi-body" class="divide-y divide-slate-100 bg-white">
            {{-- rows will be filled by JS --}}
          
          </tbody>
        </table>
      </div>
    </div>

    @include('modal.whatsapp-message')
</section>
