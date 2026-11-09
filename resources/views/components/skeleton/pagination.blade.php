@props(['paginator'])

@if ($paginator->hasPages())
    <div {{ $attributes->class('flex items-center justify-between gap-3') }}>
        <x-skeleton.line size="sm" class="hidden w-64 sm:block" />
        <div class="flex items-center gap-1">
            @foreach ($paginator->linkCollection() as $link)
                <x-skeleton.block class="size-10 rounded-md" />
            @endforeach
        </div>
    </div>
@endif
