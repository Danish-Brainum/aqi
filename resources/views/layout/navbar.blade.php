<nav class="sticky top-0 z-30 bg-gradient-to-r from-indigo-600 to-sky-600 text-white shadow">
    <div class=" mx-auto px-9 py-4 flex items-center justify-between">
      <a href="{{ route('home') }}" class="flex items-center space-x-3">
        <div class="h-9 w-9 rounded-lg bg-red/20 shadow ring-1 ring-white/30"></div>
        <span class="text-xl md:text-2xl font-bold tracking-tight">PALMONOL</span>
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
            {{-- <button type="button" id="logout-btn" class="w-full text-left px-3 py-2 rounded-xl text-sm text-slate-700 hover:bg-slate-50">Sign out</button> --}}
          </div>
        </div>
      </div>
    </div>
  </nav>