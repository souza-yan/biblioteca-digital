@props(['download'])

<tr wire:key="activity-download-{{ $download->id }}">
    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">
        {{ $download->downloaded_at->format('d/m/Y H:i') }}
    </td>
    <td class="px-4 py-3 text-sm text-gray-900">{{ $download->user->name }}</td>
    <td class="px-4 py-3 text-sm text-gray-900">{{ $download->material->title }}</td>
    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">
        {{ $download->version->version_number }}
    </td>
    <td class="px-4 py-3 text-sm text-gray-600">{{ $download->material->category->name }}</td>
</tr>
