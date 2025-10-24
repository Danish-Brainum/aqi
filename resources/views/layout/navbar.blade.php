{{-- <nav class="sticky top-0 z-30 bg-gradient-to-r from-indigo-600 to-sky-600 text-white shadow">
    <div class=" mx-auto px-9 py-4 flex items-center justify-between">
      <a href="{{ route('home') }}" class="flex items-center space-x-3">
        <div class="h-9 w-9 rounded-lg bg-red/20 shadow ring-1 ring-white/30"></div>
        <span class="text-xl md:text-2xl font-bold tracking-tight">PULMONOL</span>
      </a>
      <div class="flex items-center gap-3"> 
        <div class="relative" id="profile-menu">
          <button id="profile-button" class="inline-flex items-center rounded-xl bg-white/15 px-5 py-3 text-sm font-medium text-white shadow hover:bg-white/25 focus:outline-none focus:ring-2 focus:ring-white/40">
            {{ auth()->user()->name }}
          </button>
          <div id="profile-dropdown" class="absolute right-0 mt-2 w-44 origin-top-right rounded-lg border border-slate-200 bg-white py-1 shadow-lg hidden">
            <a href="{{ route('profile.show') }}" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Edit Profile</a>
            <form method="POST" id="logout-form" action="{{ route('logout') }}">
              @csrf
              <button type="button" id="logout-btn" data-url={{ route('saveCSV') }} class="w-full text-left px-3 py-2 rounded-xl text-sm text-slate-700 hover:bg-slate-50">Sign out</button>
             </form> 
            {{-- <button type="button" id="logout-btn" class="w-full text-left px-3 py-2 rounded-xl text-sm text-slate-700 hover:bg-slate-50">Sign out</button> --}
          </div>
        </div>
      </div>
    </div>
  </nav> --}}

  <nav class="sticky top-0 z-30 bg-gradient-to-r from-indigo-600 to-sky-600 text-white shadow">
    <div class="mx-auto px-9 py-4 flex items-center justify-between">
      <a href="{{ route('home') }}" class="flex items-center space-x-3">
        <div class="h-9 w-9 rounded-lg bg-red/20 shadow ring-1 ring-white/30"></div>
        <span class="text-xl md:text-2xl font-bold tracking-tight">PULMONOL</span>
      </a>
  
      <div class="flex items-center gap-3"> 
  
        <!-- Profile Menu -->
        <div class="relative" id="profile-menu">
          <button id="profile-button" class="inline-flex items-center rounded-xl bg-white/15 px-5 py-3 text-sm font-medium text-white shadow hover:bg-white/25 focus:outline-none focus:ring-2 focus:ring-white/40">
            {{ auth()->user()->name }}
              <!-- Chevron Down icon -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke-width="2" stroke="currentColor" class="w-4 h-4 ml-2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
          </svg>
          </button>
          <div id="profile-dropdown" class="absolute right-0 mt-2 w-44 origin-top-right rounded-lg border border-slate-200 bg-white py-1 shadow-lg hidden">
            <a href="{{ route('profile.show') }}" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Edit Profile</a>
            <form method="POST" id="logout-form" action="{{ route('logout') }}">
              @csrf
              <button type="button" id="logout-btn" class="w-full text-left px-3 py-2 rounded-xl text-sm text-slate-700 hover:bg-slate-50">Sign out</button>
            </form>
          </div>
        </div>

        <div class="relative" id="settings-menu">
          <button id="settings-button"
            class="inline-flex items-center rounded-xl bg-white/15 px-5 py-3 text-sm font-medium text-white shadow hover:bg-white/25 focus:outline-none focus:ring-2 focus:ring-white/40">
            Settings
              <!-- Chevron Down icon -->
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke-width="2" stroke="currentColor" class="w-4 h-4 ml-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>          
          </button>
        
          <form id="settings-dropdown"
                class="absolute right-0 mt-2 w-64 origin-top-right rounded-lg border border-slate-200 bg-white py-3 px-4 shadow-lg hidden">
        
              @csrf
              @method('PUT')
        
              <div class="mb-3">
                <label for="morning_time" class="block text-sm font-medium text-gray-700 mb-1">Morning Time</label>
                <input type="time" id="morning_time" name="morning_time"
                      min="00:00" max="11:59"
                      value="{{ isset($settings->morning_time) }}"
                      class="w-full rounded-md border border-gray-300 px-2 py-1 text-gray-800 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              </div>
        
              <div class="mb-3">
                <label for="evening_time" class="block text-sm font-medium text-gray-700 mb-1">Evening Time</label>
                <input type="time" id="evening_time" name="evening_time"
                      min="12:00" max="23:59"
                      value="{{ isset($settings->evening_time) }}"
                      class="w-full rounded-md border border-gray-300 px-2 py-1 text-gray-800 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                      
              </div>
        
              <button type="submit"
                      class="w-full bg-indigo-600 text-white text-sm font-medium rounded-md py-2 hover:bg-indigo-700 shadow">
                Save
              </button>
          </form>
        </div>
        
      </div>
    </div>
  </nav>
  <script>
document.addEventListener("DOMContentLoaded", function () {
    const settingsButton = document.getElementById("settings-button");
    const settingsDropdown = document.getElementById("settings-dropdown");

    // ✅ Toggle dropdown
    settingsButton.addEventListener("click", function (e) {
        e.stopPropagation();
        settingsDropdown.classList.toggle("hidden");
    });

    // ✅ Close dropdown when clicking outside
    document.addEventListener("click", function (e) {
        if (!settingsDropdown.contains(e.target) && !settingsButton.contains(e.target)) {
            settingsDropdown.classList.add("hidden");
        }
    });

    // ✅ Handle AJAX form submit
    settingsDropdown.addEventListener("submit", function (e) {
        e.preventDefault();

        const morningTime = document.getElementById("morning_time").value;
        const eveningTime = document.getElementById("evening_time").value;
        const csrfToken = document.querySelector('input[name="_token"]').value;

        fetch("{{ route('settings.update') }}", {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                morning_time: morningTime,
                evening_time: eveningTime,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // ✅ Show success message
                const msg = document.createElement("div");
                msg.textContent = "Settings saved successfully!";
                msg.className = "mt-2 text-green-600 text-sm";
                settingsDropdown.appendChild(msg);
                setTimeout(() => msg.remove(), 2500);
            } else {
                alert("Error saving settings.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Something went wrong!");
        });
    });
});


    </script>
    