@if ($actor?->isStaff())
    <div class="space-y-3 md:hidden">
        @forelse ($materials as $material)
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <x-skeleton.text class="break-words text-base font-semibold">{{ $material->title }}</x-skeleton.text>
                <x-skeleton.text class="mt-1 break-words text-sm">{{ $material->category->name }}</x-skeleton.text>
                <div class="mt-3"><x-skeleton.badge /></div>
                <div class="mt-4 flex flex-wrap justify-end gap-2">
                    @can('update', $material)
                        <x-skeleton.block class="size-12 rounded-lg" />
                        @if ($material->status !== \App\Enums\MaterialStatus::PUBLISHED)<x-skeleton.block class="size-12 rounded-lg" />@endif
                        @if ($material->status !== \App\Enums\MaterialStatus::ARCHIVED)<x-skeleton.block class="size-12 rounded-lg" />@endif
                    @endcan
                </div>
            </article>
        @empty
            <div class="rounded-xl bg-white px-4 py-8"><x-skeleton.text class="text-center text-sm">Nenhum material encontrado.</x-skeleton.text></div>
        @endforelse
    </div>
@endif
<div @class(['hidden md:block' => $actor?->isStaff()])>
<x-skeleton.table :columns="$materialColumns">
    @forelse ($materials as $material)
        <tr>
            <td class="px-5 py-4 text-lg font-medium"><x-skeleton.text>{{ $material->title }}</x-skeleton.text></td>
            <td class="px-5 py-4 text-lg"><x-skeleton.text>{{ $material->category->name }}</x-skeleton.text></td>
            <td class="px-5 py-4"><x-skeleton.text class="w-fit rounded-full px-3 py-1 text-sm font-semibold">{{ $material->status->label() }}</x-skeleton.text></td>
            @if ($actor?->isAdmin() || $actor?->isStaff())
                <td class="px-5 py-4">
                    <div class="flex flex-nowrap items-center justify-end gap-2">
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
</div>
<x-skeleton.pagination :paginator="$materials" class="mt-4" />
