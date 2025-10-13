<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign In - Pakistan AQI</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>
    .auth-card { box-shadow: 0 10px 30px rgba(79,70,229,.15); }
  </style>
  </head>
<body class="min-h-screen bg-gradient-to-b from-white via-slate-50 to-indigo-50 text-slate-800 antialiased">
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <div class="w-full rounded-2xl border border-slate-200 bg-white p-8 auth-card">
        <div class="mb-6 text-center md:text-left">
          <div class="mx-auto h-12 w-12 rounded-xl bg-brand-gradient"></div>
          <h2 class="mt-3 text-2xl text-center font-bold tracking-tight text-slate-900">Sign in to your account</h2>
        </div>

      @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
          <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
          @csrf
          <div>
            <label class="block text-sm font-medium text-slate-700">Email address
            </label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="input w-full mt-1" />
          </div>
          <div>
            {{-- <div class="flex items-center justify-between">
              <label class="block text-sm font-medium text-slate-700">Password</label>
              <a href="#" class="text-sm font-semibold  text-indigo-600 hover:underline">Forgot password?</a>
            </div> --}}
            <input type="password" name="password" required class="input w-full mt-1" />
          </div>
          <label class="inline-flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300">
            Remember me
          </label>
          <button type="submit" class="btn-primary w-full justify-center">Sign In</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
          Don't have an account?
          <a href="{{ route('register') }}" class="text-indigo-600 hover:underline font-semibold">Create one</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>


