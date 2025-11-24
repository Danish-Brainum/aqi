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
      class="absolute right-0 mt-2 w-72 origin-top-right rounded-lg border border-slate-200 bg-white py-3 px-4 shadow-lg hidden">

  @csrf
  @method('PUT')

  <!-- 🌅 Morning Time (AM only) -->
  <div class="mb-3">
    <label class="block text-sm font-medium text-gray-700 mb-1">Morning Time (AM)</label>
    <div class="flex gap-2 items-center">
      @php
        // Parse morning time from database (format: HH:MM:SS or HH:MM)
        $morningHour = '01';
        $morningMinute = '00';
        if (isset($settings) && $settings && $settings->morning_time) {
          $timeParts = explode(':', $settings->morning_time);
          $morningHour = isset($timeParts[0]) ? sprintf('%02d', (int)$timeParts[0]) : '01';
          $morningMinute = isset($timeParts[1]) ? sprintf('%02d', (int)$timeParts[1]) : '00';
        }
      @endphp
      <select id="morning_hour"
              class="w-1/2 bg-white text-gray-800 border border-gray-300 rounded-md px-2 py-1 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 cursor-pointer">
        @for ($h = 1; $h <= 11; $h++)
          <option value="{{ sprintf('%02d', $h) }}" {{ $morningHour == sprintf('%02d', $h) ? 'selected' : '' }}>{{ $h }}</option>
        @endfor
      </select>

      <select id="morning_minute"
              class="w-1/2 bg-white text-gray-800 border border-gray-300 rounded-md px-2 py-1 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 cursor-pointer">
        @for ($m = 0; $m < 60; $m += 5)
          <option value="{{ sprintf('%02d', $m) }}" {{ $morningMinute == sprintf('%02d', $m) ? 'selected' : '' }}>{{ sprintf('%02d', $m) }}</option>
        @endfor
      </select>

      <span class="text-gray-700 font-semibold">AM</span>
    </div>
  </div>

  <!-- 🌇 Evening Time (PM only) -->
  <div class="mb-3">
    <label class="block text-sm font-medium text-gray-700 mb-1">Evening Time (PM)</label>
    <div class="flex gap-2 items-center">
      @php
        // Parse evening time from database (format: HH:MM:SS or HH:MM)
        // Database stores 13-23 for PM hours, but we display 1-11
        $eveningHour = '01';
        $eveningMinute = '00';
        if (isset($settings) && $settings && $settings->evening_time) {
          $timeParts = explode(':', $settings->evening_time);
          $dbHour = isset($timeParts[0]) ? (int)$timeParts[0] : 13;
          // Convert 24-hour format (13-23) to 12-hour format (1-11) for display
          $eveningHour = $dbHour >= 13 ? sprintf('%02d', $dbHour - 12) : sprintf('%02d', $dbHour);
          $eveningMinute = isset($timeParts[1]) ? sprintf('%02d', (int)$timeParts[1]) : '00';
        }
      @endphp
      <select id="evening_hour"
              class="w-1/2 bg-white text-gray-800 border border-gray-300 rounded-md px-2 py-1 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 cursor-pointer">
        @for ($h = 1; $h <= 11; $h++)
          <option value="{{ sprintf('%02d', $h + 12) }}" {{ $eveningHour == sprintf('%02d', $h) ? 'selected' : '' }}>{{ $h }}</option>
        @endfor
      </select>

      <select id="evening_minute"
              class="w-1/2 bg-white text-gray-800 border border-gray-300 rounded-md px-2 py-1 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 cursor-pointer">
        @for ($m = 0; $m < 60; $m += 5)
          <option value="{{ sprintf('%02d', $m) }}" {{ $eveningMinute == sprintf('%02d', $m) ? 'selected' : '' }}>{{ sprintf('%02d', $m) }}</option>
        @endfor
      </select>

      <span class="text-gray-700 font-semibold">PM</span>
    </div>
  </div>

  <button type="submit"
          class="w-full bg-indigo-600 text-white text-sm font-medium rounded-md py-2 hover:bg-indigo-700 shadow">
    Save
  </button>
</form>
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

  // ✅ Close dropdown
  document.addEventListener("click", function (e) {
    if (!settingsDropdown.contains(e.target) && !settingsButton.contains(e.target)) {
      settingsDropdown.classList.add("hidden");
    }
  });

  // ✅ Handle AJAX submit
  settingsDropdown.addEventListener("submit", function (e) {
    e.preventDefault();

    const morningHour = document.getElementById("morning_hour").value;
    const morningMinute = document.getElementById("morning_minute").value;
    const eveningHour = document.getElementById("evening_hour").value;
    const eveningMinute = document.getElementById("evening_minute").value;
    const csrfToken = document.querySelector('input[name="_token"]').value;

    const morningTime = `${morningHour}:${morningMinute}`;
    const eveningTime = `${eveningHour}:${eveningMinute}`;

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


    