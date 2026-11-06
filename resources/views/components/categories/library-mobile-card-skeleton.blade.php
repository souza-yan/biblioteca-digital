@props(['showActions' => true])

{{-- Manter em sincronia com components/categories/library-mobile-card.blade.php. --}}
<article {{ $attributes->class('flex min-h-40 min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm') }}
    aria-hidden="true">
    <x-skeleton.block class="size-10 rounded-xl" />
    <x-skeleton.line size="sm" class="mt-3 w-4/5" />
    <x-skeleton.line size="xs" class="mt-auto w-20 pt-3" />

    @if ($showActions)
        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
            <x-skeleton.badge class="!w-16" />
            <div class="flex gap-2">
                <x-skeleton.button class="!size-9" />
                <x-skeleton.button class="!size-9" />
            </div>
        </div>
    @endif
</article>
