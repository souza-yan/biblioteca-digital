{{-- Manter em sincronia com o formulário saveVersion de livewire/materials/material-detail.blade.php. --}}
<div {{ $attributes->class('order-2 mt-4 space-y-4 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:order-4 sm:mt-8 sm:space-y-5 sm:p-6') }}
    aria-hidden="true">
    <div class="flex items-center gap-3">
        <x-skeleton.block class="size-11 shrink-0 rounded-xl" />
        <div class="min-w-0 flex-1 space-y-2">
            <x-skeleton.line size="sm" class="w-40" />
            <x-skeleton.line size="xs" class="w-64 max-w-full" />
        </div>
    </div>
    <x-skeleton.block class="h-20 rounded-xl" />
    <x-skeleton.block class="h-11 rounded-lg" />
    <div class="flex justify-end border-t border-slate-200 pt-4">
        <x-skeleton.button class="w-full sm:w-36" />
    </div>
</div>
