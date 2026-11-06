{{-- Manter em sincronia com livewire/materials/partials/file-preview.blade.php. --}}
<div {{ $attributes->class('mt-4 border-t border-gray-100 pt-4 sm:mt-6 sm:pt-5') }} aria-hidden="true">
    <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-3 sm:gap-3">
        <div class="space-y-2 sm:col-span-3">
            <x-skeleton.line size="xs" class="w-28" />
            <x-skeleton.line size="sm" class="w-3/5" />
        </div>
        <div class="space-y-2">
            <x-skeleton.line size="xs" class="w-12" />
            <x-skeleton.line size="sm" class="w-28" />
        </div>
        <div class="space-y-2">
            <x-skeleton.line size="xs" class="w-16" />
            <x-skeleton.line size="sm" class="w-20" />
        </div>
        <div class="hidden items-end justify-end sm:flex">
            <x-skeleton.button class="!w-44" />
        </div>
    </div>
</div>
