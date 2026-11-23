<div class="space-y-4">
    <x-skeleton.table :columns="$columns" :header-style="$tableHeaderStyle" :compact-header="true" class="max-h-[70vh] overflow-auto">
        @forelse ($records as $record)
            <tr>
                @if ($activeTab === 'downloads')
                    <td class="whitespace-nowrap px-4 py-3 text-lg"><x-skeleton.text>{{ $record->downloaded_at->inDisplayTimezone()->format('d/m/Y H:i') }}</x-skeleton.text></td>
                    <td class="px-4 py-3 text-lg font-medium"><x-skeleton.text>{{ $record->user->name }}</x-skeleton.text></td>
                    <td class="px-4 py-3 text-lg"><x-skeleton.text>{{ $record->material->title }}</x-skeleton.text></td>
                    <td class="whitespace-nowrap px-4 py-3 text-lg"><x-skeleton.text>{{ $record->version->version_number }}</x-skeleton.text></td>
                    <td class="px-4 py-3 text-lg"><x-skeleton.text>{{ $record->material->category->name }}</x-skeleton.text></td>
                @else
                    <td class="whitespace-nowrap px-4 py-3 text-lg"><x-skeleton.text>{{ $record->created_at->inDisplayTimezone()->format('d/m/Y H:i') }}</x-skeleton.text></td>
                    <td class="px-4 py-3 text-lg font-medium"><x-skeleton.text>{{ $record->user->name }}</x-skeleton.text></td>
                    <td class="whitespace-nowrap px-4 py-3"><x-skeleton.text class="w-fit rounded-full px-3 py-1 text-base font-medium">{{ $record->action->label() }}</x-skeleton.text></td>
                    <td class="px-4 py-3 text-lg"><x-skeleton.text>{{ $record->description }}</x-skeleton.text></td>
                @endif
            </tr>
        @empty
            <tr><td colspan="{{ count($columns) }}" class="px-4 py-10"><x-skeleton.text class="mx-auto w-fit text-lg">{{ $emptyMessage }}</x-skeleton.text></td></tr>
        @endforelse
    </x-skeleton.table>
    <x-skeleton.pagination :paginator="$records" />
</div>
