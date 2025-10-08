<div class="mt-3">
    {{-- 🔢 Length selector --}}
    <div class="flex items-center justify-between mb-2">
        <div>
            <label for="deleted-length" class="mr-2 text-sm text-slate-600">Show</label>
            <select id="deleted-length" class="rounded-lg border border-slate-300 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ request('perPage') == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
            </select>
            <span class="ml-1 text-sm text-slate-600">entries</span>
        </div>
    </div>

    {{-- 🔹 Table --}}
    <div class="overflow-x-auto rounded-xl border border-slate-200">
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
                            <td colspan="6" class="px-4 py-4 text-center text-base font-bold text-slate-500">
                                No Deleted Record Found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 🔹 Pagination --}}
    @if ($deleted_results->hasPages())
      <div class="mt-4 ">
        {{ $deleted_results->appends(['perPage' => request('perPage', 10)])->links('pagination::tailwind') }}
    </div>
    @endif
</div>
