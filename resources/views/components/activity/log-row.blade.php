@props([
    'log',
    'rowType',
    'badgeClasses',
])

<tr wire:key="activity-{{ $rowType }}-{{ $log->id }}">
    <td class="whitespace-nowrap px-4 py-3 text-lg text-black">
        {{ $log->created_at->format('d/m/Y H:i') }}
    </td>
    <td class="px-4 py-3 text-lg font-medium text-black">{{ $log->user->name }}</td>
    <td class="whitespace-nowrap px-4 py-3 text-lg">
        <span class="inline-flex rounded-full px-3 py-1 text-base font-medium {{ $badgeClasses[$log->action->value] }}">
            {{ $log->action->label() }}
        </span>
    </td>
    <td class="px-4 py-3 text-lg text-black">{{ $log->description }}</td>
</tr>
