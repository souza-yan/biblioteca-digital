@props(['download'])

<tr wire:key="activity-download-{{ $download->id }}">
    <td class="whitespace-nowrap px-4 py-3 text-lg text-black">
        {{ $download->downloaded_at->format('d/m/Y H:i') }}
    </td>
    <td class="px-4 py-3 text-lg font-medium text-black">{{ $download->user->name }}</td>
    <td class="px-4 py-3 text-lg text-black">{{ $download->material->title }}</td>
    <td class="whitespace-nowrap px-4 py-3 text-lg text-black">
        {{ $download->version->version_number }}
    </td>
    <td class="px-4 py-3 text-lg text-black">{{ $download->material->category->name }}</td>
</tr>
