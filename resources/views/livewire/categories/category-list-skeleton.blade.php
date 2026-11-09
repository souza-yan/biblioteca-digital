<x-skeleton.table :columns="$categoryColumns">
    @forelse ($categories as $category)
        <tr>
            <td class="px-5 py-4 text-lg font-medium"><x-skeleton.text>{{ $category->name }}</x-skeleton.text></td>
            <td class="px-5 py-4"><x-skeleton.badge /></td>
            @if ($canManageCategories)
                <td class="px-5 py-4">
                    @can('update', $category)
                        <div class="flex justify-end gap-2"><x-skeleton.block class="size-12 rounded-lg" /><x-skeleton.block class="size-12 rounded-lg" /></div>
                    @endcan
                </td>
            @endif
        </tr>
    @empty
        <tr><td colspan="{{ count($categoryColumns) }}" class="px-5 py-10"><x-skeleton.text class="mx-auto w-fit text-lg">Nenhuma categoria encontrada.</x-skeleton.text></td></tr>
    @endforelse
</x-skeleton.table>
<x-skeleton.pagination :paginator="$categories" class="mt-4" />
