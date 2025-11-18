<tr data-id="{{ $row['id'] }}" class="hover:bg-slate-50/40 transition-colors">
  <td class="px-3 py-2.5 text-sm text-slate-700 font-medium">{{ $row['id'] }}</td>
  <td class="px-3 py-2.5 text-sm text-slate-800">{{ $row['name'] }}</td>
  <td class="px-3 py-2.5 text-sm text-slate-700">
    <div class="truncate max-w-[180px]" title="{{ $row['email'] }}">{{ $row['email'] }}</div>
  </td>
  <td class="px-3 py-2.5 text-sm text-slate-700">{{ $row['city'] }}</td>
  <td class="px-3 py-2.5 text-sm text-slate-700">{{ $row['phone'] }}</td>
  <td class="px-3 py-2.5 text-sm font-semibold">
    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
      {{ ($row['aqi'] ?? 0) <= 50 ? 'bg-green-100 text-green-700' :
          (($row['aqi'] ?? 0) <= 100 ? 'bg-yellow-100 text-yellow-700' :
          'bg-red-100 text-red-700') }}">
      {{ $row['aqi'] ?? 'N/A' }}
    </span>
  </td>
</tr>
