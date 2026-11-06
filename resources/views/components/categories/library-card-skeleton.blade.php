{{-- Manter em sincronia com components/categories/library-card.blade.php. --}}
<article {{ $attributes->class('flex min-h-48 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6') }}
    aria-hidden="true">
    <x-skeleton.block class="size-12 rounded-2xl" />
    <x-skeleton.line size="lg" class="mt-4 w-2/3" />
    <div class="mt-2 flex-1 space-y-2">
        <x-skeleton.line size="sm" class="w-full" />
        <x-skeleton.line size="sm" class="w-4/5" />
    </div>
    <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
        <x-skeleton.line size="sm" class="w-36" />
        <x-skeleton.line size="sm" class="w-28" />
    </div>
</article>
