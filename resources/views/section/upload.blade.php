<section id="upload" class="tab-content">
    {{-- Top row: CSV Upload + Add Button + Tips --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  
        {{-- Tips (order first on small, third on large) --}}
        <div class="order-1 lg:order-3 rounded-2xl border border-slate-200 bg-gradient-to-br from-indigo-600 to-sky-500 p-6 text-white shadow-sm">
          <h3 class="text-lg font-semibold">Tips</h3>
          <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-indigo-50/90">
            <li>Ensure CSV headers are exactly: Name, City, Phone.</li>
            <li>Only Pakistan cities are supported.</li>
            <li>You can customize messages in the Messages tab.</li>
          </ul>
        </div>
      
        {{-- CSV Upload --}}
        <div class="order-2 lg:order-1 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4" id="upload-form">
            @csrf
            <div id="dropzone"
            class="flex flex-col sm:flex-row items-center justify-center gap-3 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-slate-500 transition hover:border-indigo-300 hover:bg-indigo-50">
          
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4" />
            </svg>
          
            {{-- Text sits above on small, inline on large --}}
            <span class="text-center sm:text-left">Drag & drop CSV here or</span>
          
            {{-- Button sits below text on small, inline on large --}}
            <label
              class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white shadow hover:bg-indigo-700">
              <input type="file" name="csv" accept=".csv" class="hidden" id="file-input" required>
              Browse
            </label>
          </div>
          
            <div id="file-error" class="mt-2 text-sm text-red-600"></div>
            <div id="file-name" class="text-sm text-slate-500"></div>
          </form>
        </div>
      
        {{-- Add Record Button --}}
        <div class="order-3 lg:order-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-center">
          <button id="open-modal"
            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            + Add Custom Record
          </button>
        </div>
      
      </div>
      
  
    {{-- Tables side by side --}}
    @php $results = $results ?? session('aqi_results', []); @endphp
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
  
  {{-- Uploaded Results Table (col-span-8) --}}
  <div class="lg:col-span-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between">
      <h3 class="text-base font-semibold text-slate-800">Uploaded Results</h3>
      <div class="flex items-center gap-3">
   
        <input 
          type="text" 
          id="tableSearch" 
          placeholder="Search..." 
          class="rounded-full border border-slate-300 px-4 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
        <a href="{{ route('download') }}" id="download-btn"
        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        Download CSV
      </a>
      <button id="open-send-modal"
  class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
  Send
</button>

      </div>
    </div>    
    
    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
      <div class="max-h-96 overflow-y-auto"> {{-- vertical scroll --}}
        <table id="results-table" class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">ID</th>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Name</th>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Email</th>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">City</th>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Phone</th>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">AQI</th>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Message</th>
              <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            @forelse($results as $i => $row)
              <tr class="hover:bg-indigo-50/40" data-id="{{ $row['id'] }}">
                <td class="px-4 py-2 text-sm">{{ $row['id'] }}</td>
                <td class="px-4 py-2 text-sm">{{ $row['name'] }}</td>
                <td class="px-4 py-2 text-sm">{{ $row['email'] }}</td>
                <td class="px-4 py-2 text-sm">{{ $row['city'] }}</td>
                <td class="px-4 py-2 text-sm">{{ $row['phone'] }}</td>
                <td class="px-4 py-2 text-sm font-semibold">
                  <span class="rounded-full px-2 py-1 text-xs
                    {{ ($row->aqi ?? 0) <= 50 ? 'bg-green-100 text-green-700' :
                        (($row->aqi ?? 0) <= 100 ? 'bg-yellow-100 text-yellow-700' :
                        'bg-red-100 text-red-700') }}">
                    {{ $row['aqi'] ?? 'N/A' }}
                  </span>
                </td>
                <td class="px-4 py-2 text-sm">{{ $row['message'] }}</td>
                <td class="px-4 py-2 text-sm">
                  <div class="inline-flex items-center gap-2">
                    <button 
                      class="edit-btn inline-flex items-center gap-2 rounded-md bg-slate-100 px-3 py-1 text-sm text-slate-700 hover:bg-slate-200"
                      data-index="{{ $i }}" 
                      data-url="{{ route('records.update') }}">
                      Edit
                    </button>
                    <button 
                      class="delete-btn inline-flex items-center gap-2 rounded-md bg-red-100 px-3 py-1 text-sm text-red-700 hover:bg-red-200" 
                      data-id="{{ $row['id'] }}" 
                      data-url="{{ route('records.delete') }}">
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr class="no-records">
                <td colspan="12" class="px-4 py-4 text-center text-base font-bold text-slate-500">
                  No CSV Found
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  
    {{-- Pagination --}}
    <div class="mt-4">
      {{ $results->links('pagination::tailwind') }}
    </div>
  </div>
  
  {{-- Deleted Records Section (col-span-4) --}}

  <div id="deleted-container" class="lg:col-span-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-base font-semibold text-slate-800">Deleted Records</h3>

    {{-- Just yield table content here --}}
    <div id="deleted-table-wrapper">
        @include('partials.deleted-table', ['deleted_results' => $deleted_results ?? []])
    </div>
  </div>
  </div>
      
  </section>
  
  
  {{-- Modal --}}
@include('modal.manual-add-record')
@include('modal.send')
