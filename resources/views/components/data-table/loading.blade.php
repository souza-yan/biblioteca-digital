@props(['colspan', 'rows' => 3])

@foreach (range(1, $rows) as $row)
    <tr wire:loading.delay wire:key="loading-row-{{ $row }}" aria-hidden="true">
        <td colspan="{{ $colspan }}" class="px-5 py-4">
            <div class="h-5 animate-pulse rounded bg-slate-200"
                style="width: {{ 55 + (($row * 13) % 35) }}%"></div>
        </td>
    </tr>
@endforeach
