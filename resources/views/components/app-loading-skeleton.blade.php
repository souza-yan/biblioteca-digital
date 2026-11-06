<div x-data="{
    visible: true,
    pendingRequests: 0,
    shownAt: performance.now(),
    minimumDuration: 300,
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
    <div class="mx-auto w-full max-w-screen-2xl space-y-6">
        <div class="flex items-start justify-between gap-6">
            <div class="space-y-3">
                <div class="app-skeleton-block h-8 w-56 rounded-xl"></div>
                <div class="app-skeleton-block h-4 w-80 max-w-[65vw] rounded-full"></div>
            </div>
            <div class="app-skeleton-block size-11 rounded-xl"></div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach (range(1, 4) as $item)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="app-skeleton-block size-12 shrink-0 rounded-2xl"></div>
                        <div class="min-w-0 flex-1 space-y-2">
                            <div class="app-skeleton-block h-4 w-2/3 rounded-full"></div>
                            <div class="app-skeleton-block h-7 w-1/3 rounded-lg"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            @foreach (range(1, 2) as $section)
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="app-skeleton-block size-10 rounded-xl"></div>
                        <div class="space-y-2">
                            <div class="app-skeleton-block h-5 w-44 rounded-full"></div>
                            <div class="app-skeleton-block h-3 w-64 max-w-[55vw] rounded-full"></div>
                        </div>
                    </div>
                    <div class="mt-6 space-y-3">
                        @foreach (range(1, 3) as $row)
                            <div class="flex items-center gap-3 rounded-xl border border-slate-200 p-3">
                                <div class="app-skeleton-block size-10 shrink-0 rounded-xl"></div>
                                <div class="min-w-0 flex-1 space-y-2">
                                    <div class="app-skeleton-block h-4 w-2/5 rounded-full"></div>
                                    <div class="app-skeleton-block h-3 w-full rounded-full"></div>
                                </div>
                                <div class="app-skeleton-block h-8 w-10 shrink-0 rounded-lg"></div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    <span class="sr-only">Carregando conteúdo</span>
</div>
