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
    closeMobileMenu() {
        this.menuOpen = false;
    },
    toggleDesktopSidebar() {
        const collapsed = document.documentElement.classList.toggle('sidebar-collapsed');

        try {
            localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
        } catch (error) {}
    },
}" @keydown.escape.window="closeMobileMenu()">
    <x-banner />

    {{-- Sidebar + Header fixos (ficam dentro do componente) --}}
    @livewire('navigation-menu')

    {{-- Conteúdo --}}
    <div id="app-main" class="min-h-screen">
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
</body>

</html>
