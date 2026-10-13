<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Biblioteca Digital de Robótica') }}</title>

    {{-- Aplica o estado da sidebar ANTES da primeira renderização --}}
    <script>
        try {
            if (localStorage.getItem('sidebarCollapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    </script>

    <style>
        #app-sidebar {
            position: fixed; left: 0; top: 0; bottom: 0; z-index: 50;
            width: 16rem; transition: width 400ms;
        }
        #app-header {
            position: fixed; top: 0; right: 0; left: 16rem; z-index: 40;
            height: 4rem; transition: left 400ms;
        }
        #app-main {
            padding-left: 16rem; transition: padding-left 400ms;
        }

        /* Textos: aparecem com atraso ao expandir, somem rápido ao recolher */
        .sidebar-label { opacity: 1; transition: opacity 200ms 200ms; }

        html.sidebar-collapsed #app-sidebar { width: 5rem; }
        html.sidebar-collapsed #app-header  { left: 5rem; }
        html.sidebar-collapsed #app-main    { padding-left: 5rem; }
        html.sidebar-collapsed .sidebar-label { opacity: 0; transition: opacity 100ms; }
    </style>

    {{-- Dependencia dos Scripts da library --}}
    <tallstackui:script />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="bg-slate-50 font-sans antialiased text-slate-900" x-data="{ menuOpen: false }">
    <x-banner />

    <div class="min-h-screen">
        <div id="app-main" class="min-h-screen pt-16">

            @if (isset($header))
                <header class="border-b border-slate-200 bg-white">
                    <div class="mx-auto max-w-screen-2xl px-4 py-5 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{ $slot }}
            </main>
        </div>

        @livewire('navigation-menu')
    </div>

    @stack('modals')

    @livewireScripts
</body>
</html>
