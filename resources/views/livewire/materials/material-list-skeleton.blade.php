<x-skeleton.table :columns="$materialColumns">
    @forelse ($materials as $material)
        <tr>
            <td class="px-5 py-4 text-lg font-medium"><x-skeleton.text>{{ $material->title }}</x-skeleton.text></td>
            <td class="px-5 py-4 text-lg"><x-skeleton.text>{{ $material->category->name }}</x-skeleton.text></td>
            <td class="px-5 py-4"><x-skeleton.text class="w-fit rounded-full px-3 py-1 text-sm font-semibold">{{ $material->status->label() }}</x-skeleton.text></td>
            @if ($actor?->isAdmin() || $actor?->isStaff())
                <td class="px-5 py-4">
                    <div class="flex flex-nowrap items-center justify-end gap-2">
                        @if ($actor?->isStaff() && $material->currentVersion)
                            @can('download', $material)<x-skeleton.block class="size-12 shrink-0 rounded-lg" />@endcan
                        @endif
                        @can('update', $material)
                            <x-skeleton.block class="size-12 shrink-0 rounded-lg" />
                            @if ($material->status !== \App\Enums\MaterialStatus::PUBLISHED)<x-skeleton.block class="size-12 shrink-0 rounded-lg" />@endif
                            @if ($material->status !== \App\Enums\MaterialStatus::ARCHIVED)<x-skeleton.block class="size-12 shrink-0 rounded-lg" />@endif
                        @endcan
                    </div>
                </td>
            @endif
        </tr>
    @empty
        <tr><td colspan="{{ count($materialColumns) }}" class="px-5 py-10"><x-skeleton.text class="mx-auto w-fit text-lg">Nenhum material encontrado.</x-skeleton.text></td></tr>
    @endforelse
</x-skeleton.table>
<x-skeleton.pagination :paginator="$materials" class="mt-4" />
