{{-- Manter em sincronia com components/dashboard/recent-access-card.blade.php. --}}
<article {{ $attributes->class('flex min-h-0 min-w-0 items-stretch overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:h-[111px]') }}
    aria-hidden="true">
    <x-skeleton.block class="h-24 w-[68px] shrink-0 rounded-none xl:h-full xl:w-[78px]" />
    <div class="flex min-w-0 flex-1 flex-col justify-center gap-3 p-3">
        <x-skeleton.line size="sm" class="w-3/4" />
        <x-skeleton.line size="xs" class="w-1/3" />
    </div>
</article>
