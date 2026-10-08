@props([
    'log',
    'rowType',
    'badgeClasses',
])

<tr wire:key="activity-{{ $rowType }}-{{ $log->id }}">
    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">
        {{ $log->created_at->format('d/m/Y H:i') }}
    </td>
    <td class="px-4 py-3 text-sm text-gray-900">{{ $log->user->name }}</td>
    <td class="whitespace-nowrap px-4 py-3 text-sm">
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $badgeClasses[$log->action->value] }}">
            {{ $log->action->label() }}
        </span>
    </td>
    <td class="px-4 py-3 text-sm text-gray-600">{{ $log->description }}</td>
</tr>
