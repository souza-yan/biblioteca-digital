<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Biblioteca Digital de Robótica') }}</title>

    {{-- Aplica o estado da sidebar ANTES da primeira renderização (evita "piscar") --}}
    <script>
        try {
            if (localStorage.getItem('sidebarCollapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    </script>

    {{-- TallStackUI: precisa vir ACIMA do @vite --}}
    <tallstackui:script />
    <x-ts-toast />
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @livewireStyles
    @vite('resources/css/app.css')
</head>

<body class="bg-slate-50 font-sans text-slate-900 antialiased" x-data="{
    menuOpen: false,
    appLoading: true,
    loadingRequests: 0,
    closeMobileMenu() {
        this.menuOpen = false;
    },
    toggleDesktopSidebar() {
        const collapsed = document.documentElement.classList.toggle('sidebar-collapsed');

        try {
            localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
        } catch (error) {}
    },
    sessionExpired: false,
}" x-init="$nextTick(() => appLoading = false)" @keydown.escape.window="closeMobileMenu()"
    x-on:session-expired.window="sessionExpired = true"
    x-on:app-loading-start.window="loadingRequests++; appLoading = true"
    x-on:app-loading-end.window="loadingRequests = Math.max(0, loadingRequests - 1); appLoading = loadingRequests > 0">
    <x-banner />

    {{-- Avisos globais para falhas que impedem novas interações Livewire. --}}
    <div wire:offline
        class="fixed inset-x-4 top-4 z-[70] rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-center text-sm font-semibold text-amber-900 shadow-lg sm:left-auto sm:max-w-md"
        role="status">
        Você está offline. As alterações serão retomadas quando a conexão voltar.
    </div>
    <div x-cloak x-show="sessionExpired" x-transition
        class="fixed inset-x-4 top-4 z-[70] rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-semibold text-red-900 shadow-lg sm:left-auto sm:max-w-md"
        role="alert">
        Sua sessão expirou. <a href="{{ route('login') }}" class="underline">Entre novamente</a> para continuar.
    </div>

    {{-- Sidebar + Header fixos (ficam dentro do componente) --}}
    @livewire(\App\Livewire\NavigationMenu::class)

    {{-- Conteúdo --}}
    <div id="app-main" class="min-h-screen">
        <div x-show="appLoading" x-transition.opacity.duration.150ms>
            <x-skeleton.page />
        </div>
        @isset($header)
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-screen-2xl px-4 py-5 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('modals')

    @livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ succeed, fail }) => {
                window.dispatchEvent(new CustomEvent('app-loading-start'));

                succeed(() => {
                    window.dispatchEvent(new CustomEvent('app-loading-end'));
                });

                fail(({ status, preventDefault }) => {
                    window.dispatchEvent(new CustomEvent('app-loading-end'));

                    if (status === 419) {
                        preventDefault();
                        window.dispatchEvent(new CustomEvent('session-expired'));
                    }
                });
            });
        });
    </script>
</body>

</html>
