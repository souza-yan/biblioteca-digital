{{-- Manter em sincronia com as listas desktop e mobile de versões em livewire/materials/material-detail.blade.php. --}}
<div {{ $attributes }} aria-hidden="true">
    <div class="hidden overflow-x-auto sm:block">
        <x-skeleton.table :rows="3" />
    </div>
    <div class="space-y-3 sm:hidden">
        @foreach (range(1, 3) as $version)
            <article class="min-h-32 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1 space-y-2">
                        <x-skeleton.line size="sm" class="w-24" />
                        <x-skeleton.line size="sm" class="w-3/4" />
                    </div>
                    <x-skeleton.button class="!size-10" />
                </div>
                <div class="mt-3 flex gap-2">
                    <x-skeleton.badge class="!w-14" />
                    <x-skeleton.line size="xs" class="w-20" />
                    <x-skeleton.line size="xs" class="w-20" />
                </div>
            </article>
        @endforeach
    </div>
</div>
