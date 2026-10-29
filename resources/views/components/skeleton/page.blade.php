<div {{ $attributes->class('app-loading-overlay overflow-y-auto bg-slate-50 p-4 sm:p-6 lg:p-8') }}
    role="status" aria-live="polite" aria-label="Carregando página">
    <div class="mx-auto w-full max-w-screen-2xl space-y-7">
        <div class="space-y-3 animate-pulse">
            <div class="skeleton-shimmer h-9 w-64 max-w-full rounded-xl"></div>
            <div class="skeleton-shimmer h-4 w-96 max-w-full rounded-full"></div>
        </div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            <x-skeleton.card />
            <x-skeleton.card />
            <x-skeleton.card class="md:col-span-2 xl:col-span-1" />
        </div>
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <x-skeleton.list :items="4" />
            <x-skeleton.form :fields="4" />
        </div>
    </div>
    <span class="sr-only">Carregando página...</span>
</div>
