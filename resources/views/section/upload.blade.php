<style>
  /* Message cell truncation with ellipsis */
  .message-cell {
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: help;
    line-height: 1.5;
    display: block;
  }
  
  /* Ensure table cells have consistent height and alignment */
  #results-table tbody td {
    vertical-align: middle;
  }
  
  /* Better table row spacing */
  #results-table tbody tr {
    height: auto;
    min-height: 48px;
  }
  
  /* Email truncation */
  #results-table tbody td .truncate {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
</style>

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
            + Add Manual Record
          </button>
        </div>
      
      </div>
      
  
    {{-- Tables side by side --}}
    @php $results = $results ?? session('aqi_results', []); @endphp
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
  
  {{-- Uploaded Results Table (col-span-8) --}}
  <div class="lg:col-span-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between">
      <h3 class="text-base font-semibold text-slate-800">Customers</h3>
      <div class="flex items-center gap-3">
      {{-- <button id="open-whatsapp-modal"
  class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
  Whatsapp
</button> --}}
{{-- <button id="open-email-modal"
  class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-3 py-2 text-white shadow hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
  Email
</button> --}}
<input 
type="text" 
id="tableSearch" 
placeholder="Search..." 
class="rounded-full border border-slate-300 px-4 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
>
<button id="open-email-modal" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-4 py-2 rounded-lg shadow transition">
  Messages
</button>

<button id="save-CSV" data-url="{{ route('saveCSV') }}" class="border border-blue-700 text-blue-700 hover:bg-blue-700 hover:text-white px-4 py-2 rounded-lg shadow transition">
  Save
</button>

<button id="send-emails-btn" data-url="{{ route('sendEmails') }}" class="border border-indigo-500 text-indigo-500 hover:bg-indigo-500 hover:text-white px-4 py-2 rounded-lg shadow transition">
  Send
</button>

<a href="{{ route('download') }}" id="download-btn" class="border border-purple-600 text-purple-600 hover:bg-purple-600 hover:text-white px-4 py-2 rounded-lg shadow transition">
  Download
</a>

      </div>
    </div>    
    
    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
      <div class="max-h-96 overflow-y-auto"> {{-- vertical scroll --}}
        <table id="results-table" class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50 sticky top-0 z-10">
            <tr>
              <th class="px-3 py-2 text-left text-xs font-semibold tracking-wide text-slate-600 w-16">ID</th>
              <th class="px-3 py-2 text-left text-xs font-semibold tracking-wide text-slate-600 min-w-[120px]">Name</th>
              <th class="px-3 py-2 text-left text-xs font-semibold tracking-wide text-slate-600 min-w-[180px]">Email</th>
              <th class="px-3 py-2 text-left text-xs font-semibold tracking-wide text-slate-600 min-w-[100px]">City</th>
              <th class="px-3 py-2 text-left text-xs font-semibold tracking-wide text-slate-600 min-w-[120px]">Phone</th>
              <th class="px-3 py-2 text-left text-xs font-semibold tracking-wide text-slate-600 w-20">AQI</th>
              <th class="px-3 py-2 text-left text-xs font-semibold tracking-wide text-slate-600 min-w-[200px] max-w-[300px]">Message</th>
              <th class="px-3 py-2 text-left text-xs font-semibold tracking-wide text-slate-600 w-32">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            @forelse($results as $i => $row)
              <tr class="hover:bg-indigo-50/40 transition-colors" data-id="{{ $row['id'] }}">
                <td class="px-3 py-2.5 text-sm text-slate-700 font-medium">{{ $row['display_id'] ?? ($i + 1) }}</td>
                <td class="px-3 py-2.5 text-sm text-slate-800">{{ $row['name'] }}</td>
                <td class="px-3 py-2.5 text-sm text-slate-700">
                  <div class="truncate max-w-[180px]" title="{{ $row['email'] }}">{{ $row['email'] }}</div>
                </td>
                <td class="px-3 py-2.5 text-sm text-slate-700">{{ $row['city'] }}</td>
                <td class="px-3 py-2.5 text-sm text-slate-700">{{ $row['phone'] }}</td>
                <td class="px-3 py-2.5 text-sm font-semibold">
                  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                    {{ ($row['aqi'] ?? 0) <= 50 ? 'bg-green-100 text-green-700' :
                        (($row['aqi'] ?? 0) <= 100 ? 'bg-yellow-100 text-yellow-700' :
                        'bg-red-100 text-red-700') }}">
                    {{ $row['aqi'] ?? 'N/A' }}
                  </span>
                </td>
                <td class="px-3 py-2.5 text-sm text-slate-700">
                  <div class="message-cell max-w-[300px] truncate" 
                       title="{{ htmlspecialchars($row['message'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                       data-full-message="{{ htmlspecialchars($row['message'] ?? '', ENT_QUOTES, 'UTF-8') }}">
                    {{ $row['message'] ?? 'N/A' }}
                  </div>
                </td>
                <td class="px-3 py-2.5 text-sm">
                  <div class="inline-flex items-center gap-1.5">
                    <button 
                      class="edit-btn inline-flex items-center justify-center rounded-md bg-slate-100 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200 transition-colors"
                      data-index="{{ $i }}" 
                      data-url="{{ route('records.update') }}">
                      Edit
                    </button>
                    <button 
                      class="delete-btn inline-flex items-center justify-center rounded-md bg-red-100 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200 transition-colors" 
                      data-id="{{ $row['id'] }}" 
                      data-url="{{ route('records.delete') }}">
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr class="no-records">
                <td colspan="8" class="px-4 py-8 text-center text-base font-semibold text-slate-500">
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
    <h3 class="text-base font-semibold text-slate-800">Deleted Customers</h3>

    {{-- Just yield table content here --}}
    <div id="deleted-table-wrapper">
        @include('partials.deleted-table', ['deleted_results' => $deleted_results ?? []])
    </div>
  </div>
  </div>
      
  </section>
  
  
  {{-- Modal --}}
@include('modal.manual-add-record')
@include('modal.whatsapp')
@include('modal.email-message')
