@props([
    'colspan',
    'compact' => false,
])

<tr>
    <td
        colspan="{{ $colspan }}"
        @class([
            'px-4 py-10 text-center text-sm text-gray-500' => $compact,
            'px-5 py-10 text-center text-sm text-gray-500' => ! $compact,
        ])
    >
        {{ $slot }}
    </td>
</tr>
