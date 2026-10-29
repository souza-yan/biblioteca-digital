@props(['colspan', 'rows' => 3])

@foreach (range(1, $rows) as $row)
    <tr wire:loading.delay wire:key="loading-row-{{ $row }}" aria-hidden="true">
        <td colspan="{{ $colspan }}" class="px-5 py-4">
            <x-skeleton :lines="1" class="py-0" />
        </td>
    </tr>
@endforeach
