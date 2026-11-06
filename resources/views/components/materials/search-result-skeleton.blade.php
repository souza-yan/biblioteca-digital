{{-- Manter em sincronia com components/materials/search-result.blade.php. --}}
<div {{ $attributes->class('flex min-w-0 items-center gap-3 border-b border-slate-100 px-4 py-3 sm:px-5 sm:odd:border-r') }}
    aria-hidden="true">
    <x-skeleton.block class="h-16 w-11 shrink-0 rounded-lg" />
    <span class="min-w-0 flex-1 space-y-2">
        <x-skeleton.line size="sm" class="w-4/5" />
        <x-skeleton.line size="xs" class="w-1/2" />
    </span>
</div>
