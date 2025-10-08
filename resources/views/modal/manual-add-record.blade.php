<div id="record-modal"
class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
<div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-lg">
  <div class="flex items-center justify-between border-b pb-3">
    <h2 class="text-lg font-semibold text-slate-900">Add Manual Record</h2>
    <button id="close-modal" class="text-slate-500 hover:text-slate-700">
      ✕
    </button>
  </div>

  <form method="POST" action="{{ route('add-manual-record') }}" class="mt-4 space-y-4">
    @csrf
    <div>
      <label class="block text-sm font-medium text-slate-700">Name</label>
      <input type="text" name="name" value="{{ old('name') }}" required
        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700">Email</label>
      <input type="text" name="email" value="{{ old('email') }}" required
        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700">City</label>
      <input type="text" name="city" value="{{ old('city') }}" required
        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700">Phone</label>
      <input type="text" name="phone" required
        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
    </div>

    
    <div class="flex justify-end">
      <button type="submit"
        class="rounded-lg bg-indigo-700 px-4 py-2 text-white shadow hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-600">
        Save Record
      </button>
    </div>
  </form>
</div>
</div>