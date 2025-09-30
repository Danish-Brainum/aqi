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