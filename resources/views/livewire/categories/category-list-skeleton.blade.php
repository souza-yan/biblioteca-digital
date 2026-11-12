@if ($actor?->isStaff())
    <div class="space-y-3 md:hidden">
        @forelse ($categories as $category)
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <x-skeleton.text class="break-words text-base font-semibold">{{ $category->name }}</x-skeleton.text>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <x-skeleton.badge />
                    <div class="flex gap-2"><x-skeleton.block class="size-12 rounded-lg" /><x-skeleton.block class="size-12 rounded-lg" /></div>
                </div>
            </article>
        @empty
            <div class="rounded-xl bg-white px-4 py-8"><x-skeleton.text class="text-center text-sm">Nenhuma categoria encontrada.</x-skeleton.text></div>
        @endforelse
    </div>
@endif
<div @class(['hidden md:block' => $actor?->isStaff()])>
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
</div>
<x-skeleton.pagination :paginator="$categories" class="mt-4" />
