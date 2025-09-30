<section id="analytics" class="tab-content hidden">
    {{-- <button id="fetchAll" class="px-4 py-2 bg-green-600 text-white rounded">Fetch AQI</button>

    <table id="cityTable" class="mt-4 w-full border">
      <thead>
        <tr class="bg-gray-200">
          <th class="px-2 py-1">City</th>
          <th class="px-2 py-1">AQI</th>
        </tr>
      </thead>
      <tbody>
        
      </tbody>
    </table> --}}
    <div id="alert-container"></div>

    <div class="mt-4">
        <button id="fetchAll" class=" rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700">
          Fetch AQI
        </button>
      </div>
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
  
  
</section>
