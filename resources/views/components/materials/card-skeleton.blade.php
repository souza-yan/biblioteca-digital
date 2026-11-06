@props([
    'variant' => 'library',
    'actions' => 2,
])

@if ($variant === 'featured')
    {{-- Manter em sincronia com components/materials/card.blade.php (variant featured). --}}
    <article {{ $attributes->class('group relative w-full shrink-0 snap-start overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:w-auto') }}
        aria-hidden="true">
        <div class="p-3">
            <x-skeleton.block class="mx-auto aspect-[2480/3508] w-48 rounded-xl sm:w-full" />
        </div>

        <div class="p-4 pt-1">
            <x-skeleton.line size="sm" class="w-1/3" />
            <div class="mt-1 flex items-center gap-3">
                <x-skeleton.line class="w-4/5" />
                <x-skeleton.button class="!size-9 sm:absolute sm:right-5 sm:top-5" />
            </div>
            <div class="mt-3 flex items-center justify-between gap-3">
                <x-skeleton.line size="xs" class="w-20" />
                <x-skeleton.badge class="!w-12" />
            </div>
        </div>
    </article>
@else
    {{-- Manter em sincronia com components/materials/card.blade.php (variant library). --}}
    <article {{ $attributes->class('flex min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-3 shadow-sm') }}
        aria-hidden="true">
        <x-skeleton.block class="aspect-[2480/3508] w-full rounded-lg" />

        <div class="min-w-0 space-y-2 pt-3">
            <x-skeleton.line size="sm" class="w-4/5" />
            <x-skeleton.line size="xs" class="w-2/5" />
        </div>

        <div class="mt-3 flex items-center justify-end gap-1.5 border-t border-slate-100 pt-3">
            @foreach (range(1, $actions) as $action)
                <x-skeleton.button class="!size-9" />
            @endforeach
        </div>
    </article>
@endif
