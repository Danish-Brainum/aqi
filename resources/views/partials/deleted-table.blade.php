<div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
  <div class="max-h-96 overflow-y-auto">
      <table id="deleted-table" class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
              <tr>
                  <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">ID</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Name</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Email</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">City</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Phone</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">AQI</th>
              </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
              @forelse($deleted_results as $row)
                  @include('partials.deleted-rows', ['row' => $row])
              @empty
                  <tr class="no-records">
                      <td colspan="5" class="px-4 py-4 text-center text-base font-bold text-slate-500">
                          No Deleted Record Found
                      </td>
                  </tr>
              @endforelse
          </tbody>
      </table>
  </div>
</div>

{{-- 🔹 Make sure pagination is always rendered with Tailwind --}}
@if ($deleted_results->hasPages())
  <div class="mt-4">
      {{ $deleted_results->links('pagination::tailwind') }}
  </div>
@endif
