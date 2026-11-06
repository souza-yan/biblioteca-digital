<div x-data="{
    visible: true,
    pendingRequests: 0,
    shownAt: performance.now(),
    minimumDuration: 350,
    start() {
        if (this.pendingRequests === 0) {
            this.visible = true;
            this.shownAt = performance.now();
        }

        this.pendingRequests++;
    },
    finish() {
        this.pendingRequests = Math.max(0, this.pendingRequests - 1);

        if (this.pendingRequests > 0) {
            return;
        }

        window.setTimeout(() => {
            if (this.pendingRequests === 0) {
                this.visible = false;
            }
        }, Math.max(0, this.minimumDuration - (performance.now() - this.shownAt)));
    },
    initialize() {
        window.setTimeout(() => this.visible = false, this.minimumDuration);

        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ succeed, fail }) => {
                this.start();
                succeed(() => this.finish());
                fail(() => this.finish());
            });
        });
    },
}" x-init="initialize()" x-show="visible" x-transition.opacity.duration.150ms
    class="fixed inset-0 z-[90] overflow-hidden bg-slate-50 px-4 py-6 sm:px-6 lg:px-8" role="status" aria-live="polite"
    aria-label="Carregando conteúdo">
    @php
        $variant = match (true) {
            request()->routeIs('dashboard') => 'dashboard',
            request()->routeIs('painel.library', 'painel.favorites') => 'library',
            request()->routeIs('painel.library.categories', 'painel.categories') => 'categories',
            request()->routeIs('painel.library.show', 'painel.materials.show') => 'detail',
            request()->routeIs('painel.materials', 'painel.users', 'painel.schools', 'painel.activities') => 'manager',
            default => 'form',
        };
    @endphp

    <div class="mx-auto w-full max-w-screen-2xl space-y-6">
        @if ($variant === 'dashboard')
            <div class="space-y-3 rounded-3xl bg-blue-800 p-6 sm:p-8">
                <div class="app-skeleton-block h-9 w-80 max-w-[75vw] rounded-xl"></div>
                <div class="app-skeleton-block h-5 w-96 max-w-full rounded-full"></div>
                <div class="app-skeleton-block mt-6 h-12 w-full rounded-xl bg-white/70"></div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach (range(1, 4) as $item)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="app-skeleton-block size-12 shrink-0 rounded-2xl"></div>
                            <div class="min-w-0 flex-1 space-y-2"><div class="app-skeleton-block h-4 w-2/3 rounded-full"></div><div class="app-skeleton-block h-7 w-1/3 rounded-lg"></div></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                @foreach (range(1, 2) as $section)
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex items-center gap-3"><div class="app-skeleton-block size-10 rounded-xl"></div><div class="space-y-2"><div class="app-skeleton-block h-5 w-44 rounded-full"></div><div class="app-skeleton-block h-3 w-64 max-w-[55vw] rounded-full"></div></div></div>
                        <div class="mt-6 space-y-3">
                            @foreach (range(1, 3) as $row)
                                <div class="flex items-center gap-3 rounded-xl border border-slate-200 p-3"><div class="app-skeleton-block size-10 shrink-0 rounded-xl"></div><div class="min-w-0 flex-1 space-y-2"><div class="app-skeleton-block h-4 w-2/5 rounded-full"></div><div class="app-skeleton-block h-3 w-full rounded-full"></div></div><div class="app-skeleton-block h-8 w-10 shrink-0 rounded-lg"></div></div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @elseif ($variant === 'library')
            <div class="flex items-center justify-between gap-4"><div class="app-skeleton-block h-10 w-72 rounded-xl"></div><div class="app-skeleton-block size-10 rounded-xl"></div></div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"><div class="app-skeleton-block h-12 rounded-xl"></div><div class="app-skeleton-block h-12 rounded-xl"></div><div class="app-skeleton-block h-12 rounded-xl"></div></div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach (range(1, 8) as $card)
                    <article class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm"><div class="app-skeleton-block aspect-[2480/3508] rounded-lg"></div><div class="space-y-2 pt-3"><div class="app-skeleton-block h-4 w-4/5 rounded-full"></div><div class="app-skeleton-block h-3 w-2/5 rounded-full"></div></div><div class="mt-3 flex justify-end gap-2 border-t border-slate-100 pt-3"><div class="app-skeleton-block size-9 rounded-lg"></div><div class="app-skeleton-block size-9 rounded-lg"></div></div></article>
                @endforeach
            </div>
        @elseif ($variant === 'categories')
            <div class="flex items-center justify-between gap-4"><div class="app-skeleton-block h-10 w-64 rounded-xl"></div><div class="app-skeleton-block h-11 w-36 rounded-xl"></div></div>
            <div class="app-skeleton-block h-12 w-full rounded-xl"></div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (range(1, 6) as $card)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex gap-3"><div class="app-skeleton-block size-11 shrink-0 rounded-xl"></div><div class="flex-1 space-y-2"><div class="app-skeleton-block h-5 w-3/4 rounded-full"></div><div class="app-skeleton-block h-3 w-full rounded-full"></div></div></div><div class="mt-5 flex justify-between border-t border-slate-100 pt-3"><div class="app-skeleton-block h-7 w-24 rounded-full"></div><div class="app-skeleton-block size-9 rounded-lg"></div></div></article>
                @endforeach
            </div>
        @elseif ($variant === 'detail' || $variant === 'form')
            <div class="flex items-start justify-between gap-6"><div class="space-y-3"><div class="app-skeleton-block h-9 w-80 max-w-[70vw] rounded-xl"></div><div class="app-skeleton-block h-4 w-48 rounded-full"></div></div><div class="app-skeleton-block size-11 rounded-xl"></div></div>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><div class="space-y-5"><div class="app-skeleton-block h-5 w-32 rounded-full"></div><div class="app-skeleton-block h-12 w-full rounded-xl"></div><div class="app-skeleton-block h-5 w-40 rounded-full"></div><div class="app-skeleton-block h-28 w-full rounded-xl"></div><div class="flex justify-end"><div class="app-skeleton-block h-11 w-36 rounded-xl"></div></div></div></section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><div class="app-skeleton-block h-6 w-32 rounded-full"></div><div class="mt-5 space-y-3">@foreach (range(1, 3) as $row)<div class="app-skeleton-block h-16 w-full rounded-xl"></div>@endforeach</div></section>
        @else
            <div class="flex items-center justify-between gap-4"><div class="app-skeleton-block h-10 w-64 rounded-xl"></div><div class="app-skeleton-block h-11 w-36 rounded-xl"></div></div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3"><div class="app-skeleton-block h-12 rounded-xl md:col-span-2"></div><div class="app-skeleton-block h-12 rounded-xl"></div></div>
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="app-skeleton-block h-12 w-full rounded-none"></div><div class="space-y-0 divide-y divide-slate-100">@foreach (range(1, 6) as $row)<div class="flex items-center gap-4 p-4"><div class="app-skeleton-block size-10 rounded-xl"></div><div class="flex-1 space-y-2"><div class="app-skeleton-block h-4 w-1/3 rounded-full"></div><div class="app-skeleton-block h-3 w-2/3 rounded-full"></div></div><div class="app-skeleton-block size-9 rounded-lg"></div></div>@endforeach</div></section>
        @endif
    </div>

    <span class="sr-only">Carregando conteúdo</span>
</div>
