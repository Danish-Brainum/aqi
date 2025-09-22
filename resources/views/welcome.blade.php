<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pakistan AQI Dashboard</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <script src="//unpkg.com/alpinejs" defer></script>
  <style>
  [x-cloak] { display: none !important; }
</style>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-sky-50 to-white text-slate-800 antialiased" x-data="{ loading: false }">
   <!-- Overlay Loader -->
    <!-- Overlay Loader -->
    <div 
        x-show="loading" 
        x-cloak
        class="fixed inset-0 flex items-center justify-center bg-slate-900/60 z-50"
        x-transition.opacity
    >
        <div class="flex flex-col items-center gap-4">
            <svg class="animate-spin h-12 w-12 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <p class="text-white font-medium text-lg">Processing...</p>
        </div>
    </div>


  <header class="sticky top-0 z-30 bg-gradient-to-r from-indigo-600 to-sky-600 text-white shadow">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <a href="{{ route('home') }}" class="flex items-center space-x-3">
        <div class="h-9 w-9 rounded-lg bg-red/20 shadow ring-1 ring-white/30"></div>
        <span class="text-xl md:text-2xl font-bold tracking-tight">Pakistan AQI</span>
      </a>
      <div class="flex items-center gap-3"> 
        <div class="relative" id="profile-menu">
          <button id="profile-button" class="inline-flex items-center rounded-xl bg-white/15 px-5 py-3 text-sm font-medium text-white shadow hover:bg-white/25 focus:outline-none focus:ring-2 focus:ring-white/40">
            {{ auth()->user()->name }}
          </button>
          <div id="profile-dropdown" class="absolute right-0 mt-2 w-44 origin-top-right rounded-lg border border-slate-200 bg-white py-1 shadow-lg hidden">
            <a href="{{ route('profile.show') }}" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Edit Profile</a>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="w-full text-left px-3 py-2 rounded-xl text-sm text-slate-700 hover:bg-slate-50">Sign out</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-6 py-10">
    <div class="rounded-2xl bg-blue-300 border border-slate-200 px-6 py-8 mb-8 text-center">
      <h1 class="text-2xl font-bold text-white">Pakistan AQI Dashboard</h1>
      <p class="mt-1 text-sm text-white">Upload CSV, view AQI results, and manage messages</p>
    </div>
    @if (session('success'))
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
    @endif
    @if (session('error'))
      <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <div class="font-semibold">{{ session('error') }}</div>
        @if (session('error_details'))
          <div class="mt-1 text-red-700/90">{{ session('error_details') }}</div>
        @endif
      </div>
    @endif
    @if ($errors->any())
      <div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
        <div class="font-semibold">There were some problems with your upload:</div>
        <ul class="mt-2 list-disc pl-5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    <div class="mb-6">
      <div class="inline-flex rounded-xl bg-slate-100 p-1 shadow-sm">
        <button data-tab="upload" class="tab-btn rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:text-indigo-700 data-[active=true]:bg-white data-[active=true]:text-indigo-700 data-[active=true]:shadow">
          Upload
        </button>
        <button data-tab="messages" class="tab-btn rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:text-indigo-700">
          Messages
        </button>
        <button data-tab="analytics" class="tab-btn rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:text-indigo-700">
          Analytics
        </button>
      </div>
    </div>

    <section id="upload" class="tab-content">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
          <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-lg font-semibold text-slate-900">Upload CSV & Check AQI</h2>
              </div>
            </div>

            <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4" id="upload-form">
              @csrf
              <div id="dropzone" class="flex items-center justify-center gap-3 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-slate-500 transition hover:border-indigo-300 hover:bg-indigo-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4"/></svg>
                <span class="hidden sm:inline">Drag & drop CSV here or</span>
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white shadow hover:bg-indigo-700">
                  <input type="file" name="csv" accept=".csv" class="hidden" id="file-input" required>
                  Browse
                </label>
              </div>
              <div id="file-error" class="mt-2 text-sm text-red-600"></div>
              <div class="flex items-center justify-start">
                <div id="file-name" class="text-sm text-slate-500"></div>
              </div>
            </form>
          </div>
        </div>
        <div>
          <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-indigo-600 to-sky-500 p-6 text-white shadow-sm">
            <h3 class="text-lg font-semibold">Tips</h3>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-indigo-50/90">
              <li>Ensure CSV headers are exactly: Name, City, Phone.</li>
              <li>Only Pakistan cities are supported.</li>
              <li>You can customize messages in the Messages tab.</li>
            </ul>
          </div>
        </div>
       
        @php $results = $results ?? session('aqi_results', []); @endphp
        <div class="lg:col-span-2">
          <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <h3 class="text-base font-semibold text-slate-800"></h3>
              <a href="{{ route('download') }}" id="download-btn"
                class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-3 py-2 text-white shadow hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                Download CSV
              </a>
            </div>

            <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">
              <table id="results-table" class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">ID</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Name</th>
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
                      <td class="px-4 py-2 text-sm">{{ $row['city'] }}</td>
                      <td class="px-4 py-2 text-sm">{{ $row['phone'] }}</td>
                      <td class="px-4 py-2 text-sm font-semibold">
                        <span class="rounded-full px-2 py-1 text-xs
                          {{ ($row['aqi'] ?? 0) <= 50 ? 'bg-green-100 text-green-700' :
                              (($row['aqi'] ?? 0) <= 100 ? 'bg-yellow-100 text-yellow-700' :
                              'bg-red-100 text-red-700') }}">
                          {{ $row['aqi'] ?? 'N/A' }}
                        </span>
                      </td>
                      <td class="px-4 py-2 text-sm">{{ $row['message'] }}</td>
                      <td class="px-4 py-2 text-sm">
                        <div class="inline-flex items-center gap-2">
                          <button class="edit-btn inline-flex items-center gap-2 rounded-md bg-slate-100 px-3 py-1 text-sm text-slate-700 hover:bg-slate-200 data-index="{{ $i }}" data-url="{{ route('records.update') }}">Edit</button>
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
        </div>
        {{-- Deleted Records Table --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="text-base font-semibold text-slate-800 mt-4">Deleted Records</h3>
          <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">
            <table id="deleted-table" class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">ID</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Name</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">City</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Phone</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">AQI</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white">
                @forelse(session('deleted_results', []) as $row)
                  @include('partials.deleted-rows', ['row' => $row])
                @empty
                <tr class="no-records">
                    <td colspan="12" class="px-4 py-4 text-center text-base font-bold text-slate-500">
                      No Deleted Record Found
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm mt-4" >
        <h2 class="text-lg font-semibold text-slate-900">Add Custom Record</h2>
        <p class="mt-1 text-sm text-slate-500">Override default texts per AQI range</p>
        <div class="row">

        <form method="POST" action="{{ route('upload') }}" >
          @csrf
          <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="col-md-6">
              <label class="block text-sm font-medium text-slate-700 ">Name</label>
              <input type="text" name="name" value="{{ old('name') }}" required 
                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
            </div>
            <div class="col-md-6">
              <label class="block text-sm font-medium text-slate-700">City</label>
              <input type="text" name="city" value="{{ old('city') }}" required class="input w-full mt-1" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Phone</label>
              <input type="text" name="phone" required class="input w-full mt-1" />
            </div>
            </div>
            <div>
              <button type="submit" class="btn-primary w-full justify-center mt-4">Create Record</button>
            </div>

        </form>
      </div>
    </div>
  </div>
  </section>

    <section id="messages" class="tab-content hidden">
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Custom AQI Messages</h2>
        <p class="mt-1 text-sm text-slate-500">Override default texts per AQI range</p>
        <form method="POST" action="{{ route('save_messages') }}" class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
          @csrf
          <input type="text" name="good" placeholder="Good (0-50)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <input type="text" name="moderate" placeholder="Moderate (51-100)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <input type="text" name="unhealthy_sensitive" placeholder="Unhealthy Sensitive (101-150)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <input type="text" name="unhealthy" placeholder="Unhealthy (151-200)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <input type="text" name="very_unhealthy" placeholder="Very Unhealthy (201-300)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <input type="text" name="hazardous" placeholder="Hazardous (301+)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-white shadow hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
              Save Messages
            </button>
          </div>
        </form>
      </div>
    </section>

    <section id="analytics" class="tab-content hidden">
      <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="font-semibold text-slate-900">AQI Overview</h3>
          <p class="mt-1 text-sm text-slate-500">Coming soon...</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="font-semibold text-slate-900">City Comparisons</h3>
          <p class="mt-1 text-sm text-slate-500">Coming soon...</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="font-semibold text-slate-900">Trends</h3>
          <p class="mt-1 text-sm text-slate-500">Coming soon...</p>
        </div>
      </div>
    </section>
   <!-- Edit Modal -->
   <!-- Edit Modal -->
<!-- Edit Modal -->
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


</div>

    </form>
  </div>
</div>
  </main>

  <footer class="border-t border-slate-200/60 bg-white">
    <div class="max-w-7xl mx-auto px-6 py-6 text-center text-sm text-slate-500">
      Built with ❤️ for clean air awareness
    </div>
  </footer>
</body>
</html>
