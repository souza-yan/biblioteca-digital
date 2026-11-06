{{-- Manter em sincronia com components/dashboard/category-card.blade.php. --}}
<div {{ $attributes->class('flex w-full shrink-0 snap-start items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:w-[calc((100%_-_0.75rem)_/_2)] xl:w-[calc((100%_-_1.5rem)_/_3)]') }}
    aria-hidden="true">
    <x-skeleton.block class="size-11 shrink-0 rounded-xl" />
    <span class="min-w-0 flex-1 space-y-2">
        <x-skeleton.line size="sm" class="w-1/2" />
        <x-skeleton.line size="xs" class="w-2/3" />
    </span>
</div>
