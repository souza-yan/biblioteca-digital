@props(['material', 'actions' => 2, 'variant' => 'library', 'date' => null, 'type' => null])

@if ($variant === 'featured')
    <article {{ $attributes->class('relative w-full shrink-0 snap-start overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:w-auto') }} aria-hidden="true">
        <div class="p-3"><x-skeleton.block class="mx-auto aspect-[2480/3508] w-48 rounded-xl sm:w-full" /></div>
        <div class="p-4 pt-1">
            <x-skeleton.text class="truncate text-sm font-medium">{{ $material->category?->name ?? 'Sem categoria' }}</x-skeleton.text>
            <div class="mt-1 flex items-center gap-3">
                <x-skeleton.text class="min-w-0 flex-1 truncate text-base font-bold">{{ $material->title }}</x-skeleton.text>
                <x-skeleton.block class="size-10 shrink-0 rounded-lg sm:absolute sm:right-5 sm:top-5" />
            </div>
            @if ($date || $type)
                <div class="mt-3 flex items-center justify-between gap-3 text-xs">
                    <x-skeleton.text>{{ $date }}</x-skeleton.text>
                    @if ($type)<x-skeleton.text class="rounded-full px-2 py-1 font-semibold">{{ $type }}</x-skeleton.text>@endif
                </div>
            @endif
        </div>
    </article>
@else
    <article {{ $attributes->class('flex min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-3 shadow-sm') }} aria-hidden="true">
        <x-skeleton.block class="aspect-[2480/3508] w-full rounded-xl" />
        <div class="min-w-0 pt-3">
            <x-skeleton.text class="break-words text-sm font-semibold leading-5">{{ $material->title }}</x-skeleton.text>
            <x-skeleton.text class="mt-1 break-words text-xs">{{ $material->category?->name ?? 'Sem categoria' }}</x-skeleton.text>
        </div>
        @if ($actions > 0)
            <div class="mt-3 flex flex-wrap items-center justify-end gap-1.5 border-t border-slate-100 pt-3 sm:flex-nowrap">
                @for ($action = 0; $action < $actions; $action++)
                    <x-skeleton.block class="size-11 shrink-0 rounded-lg" />
                @endfor
            </div>
        @endif
    </article>
@endif
