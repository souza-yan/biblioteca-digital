@php
    $variant = match (true) {
        request()->routeIs('dashboard') => 'dashboard',
        request()->routeIs('painel.library', 'painel.favorites') => 'material-grid',
        request()->routeIs('painel.library.categories', 'painel.categories') => 'category-grid',
        request()->routeIs('painel.library.show', 'painel.materials.show') => 'material-detail',
        request()->routeIs('profile.show') => 'profile',
        request()->routeIs('painel.materials', 'painel.materials.most-downloaded', 'painel.users', 'painel.schools', 'painel.activities') => 'management-list',
        default => 'fallback',
    };

    $component = match ($variant) {
        'dashboard' => 'skeleton.dashboard',
        'material-grid' => 'skeleton.material-grid',
        'category-grid' => 'skeleton.category-grid',
        'material-detail' => 'skeleton.material-detail',
        'profile' => 'skeleton.profile',
        default => 'skeleton.management-list',
    };
@endphp

<div x-data="{
    visible: true,
    pendingRequests: 1,
    shownAt: performance.now(),
    minimumDuration: 350,
    hideTimer: null,
    start() {
        window.clearTimeout(this.hideTimer);
        this.hideTimer = null;

        if (this.pendingRequests === 0) {
            this.visible = true;
            this.shownAt = performance.now();
        }

        this.pendingRequests++;
    },
    finish() {
        this.pendingRequests = Math.max(0, this.pendingRequests - 1);

        if (this.pendingRequests !== 0) return;

        window.clearTimeout(this.hideTimer);
        const remaining = Math.max(0, this.minimumDuration - (performance.now() - this.shownAt));
        this.hideTimer = window.setTimeout(() => {
            if (this.pendingRequests === 0) this.visible = false;
        }, remaining);
    },
    init() {
        this.navigationStarted = () => this.start();
        this.navigationFinished = () => this.finish();
        this.externalStarted = () => this.start();
        this.externalFinished = () => this.finish();
        document.addEventListener('livewire:navigating', this.navigationStarted);
        document.addEventListener('livewire:navigated', this.navigationFinished);
        window.addEventListener('app-loading:start', this.externalStarted);
        window.addEventListener('app-loading:finish', this.externalFinished);
        queueMicrotask(() => this.finish());
    },
    destroy() {
        window.clearTimeout(this.hideTimer);
        document.removeEventListener('livewire:navigating', this.navigationStarted);
        document.removeEventListener('livewire:navigated', this.navigationFinished);
        window.removeEventListener('app-loading:start', this.externalStarted);
        window.removeEventListener('app-loading:finish', this.externalFinished);
    },
}" x-show="visible" x-transition.opacity.duration.150ms x-bind:aria-hidden="(! visible).toString()"
    x-bind:aria-busy="visible.toString()" data-app-loading-skeleton data-skeleton-variant="{{ $variant }}"
    class="app-loading-overlay fixed z-30 overflow-y-auto bg-slate-50" role="status" aria-live="polite"
    aria-label="Carregando conteúdo">
    <x-dynamic-component :component="$component" :mode="$variant" />
    <span class="sr-only">Carregando conteúdo</span>
</div>
