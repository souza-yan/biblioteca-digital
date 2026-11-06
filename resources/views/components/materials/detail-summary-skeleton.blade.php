@props([
    'isTeacher' => false,
    'hasCurrentVersion' => false,
])

{{-- Manter em sincronia com o primeiro article de livewire/materials/material-detail.blade.php. --}}
<article {{ $attributes->class('rounded-md border border-gray-200 bg-white p-4 shadow-sm sm:p-6') }} aria-hidden="true">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start sm:gap-4">
        <div class="space-y-2">
            <x-skeleton.line size="xl" class="w-80 max-w-[70vw]" />
            @if ($isTeacher)
                <x-skeleton.line size="sm" class="w-40" />
            @endif
        </div>
        <div class="flex shrink-0 gap-2">
            @if ($isTeacher)
                <x-skeleton.button class="!size-10 sm:!size-12" />
                @if ($hasCurrentVersion)
                    <x-skeleton.button class="!size-10 sm:!size-12" />
                    <x-skeleton.button class="!size-10 sm:!size-12 sm:hidden" />
                @endif
            @endif
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-2 border-t border-gray-100 pt-4 sm:mt-6 sm:grid-cols-2 sm:gap-4 sm:pt-5">
        <div class="space-y-2">
            <x-skeleton.line size="xs" class="w-16" />
            <x-skeleton.badge class="!w-24" />
        </div>
        @if ($isTeacher)
            <div class="hidden space-y-2 sm:block">
                <x-skeleton.line size="xs" class="w-24" />
                <x-skeleton.line size="sm" class="w-40" />
            </div>
        @endif
        <div class="space-y-2 sm:col-span-2">
            <x-skeleton.line size="xs" class="w-20" />
            <x-skeleton.line size="sm" class="w-full" />
            <x-skeleton.line size="sm" class="w-3/4" />
        </div>
        @if ($isTeacher && $hasCurrentVersion)
            <div class="hidden space-y-2 sm:block">
                <x-skeleton.line size="xs" class="w-24" />
                <x-skeleton.line size="sm" class="w-56" />
            </div>
        @endif
    </div>

    @if ($isTeacher && $hasCurrentVersion)
        <x-materials.file-preview-skeleton />
    @endif
</article>
