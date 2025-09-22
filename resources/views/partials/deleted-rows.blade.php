<tr data-id="{{ $row['id'] }}">
  <td class="px-4 py-2 text-sm">{{ $row['id'] }}</td>
  <td class="px-4 py-2 text-sm">{{ $row['name'] }}</td>
  <td class="px-4 py-2 text-sm">{{ $row['city'] }}</td>
  <td class="px-4 py-2 text-sm">{{ $row['phone'] }}</td>
  <td class="px-4 py-2 text-sm font-semibold">
    <span class="rounded-full px-2 py-1 text-xs
      {{ ($row['aqi'] ?? 0) <= 50 ? 'bg-green-100 text-green-700' :
          (($row['aqi'] ?? 0) <= 100 ? 'bg-yellow-100 text-yellow-700' :
          'bg-red-100 text-red-700') }}">
      {{ $row['aqi'] ?? 'N/A' }}
    </span>
  </td>
  <td class="px-4 py-2 text-sm">{{ $row['message'] ?? '' }}</td>
</tr>

