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
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-sky-50 to-white text-slate-800 antialiased relative">

  <div class="pb-24"> <!-- Pushes footer down when content is short -->
    @include('layout.navbar')

    <main class="mx-auto px-6 py-10">
      @include('layout.header')
      @yield('content')
    </main>
  </div>

  <footer class="absolute bottom-0 left-0 w-full border-t border-slate-200/60 bg-white">
    <div class="max-w-7xl mx-auto px-6 py-6 text-center text-sm text-slate-500">
      Built with ❤️ for clean air awareness
    </div>
  </footer>

</body>

</html>
