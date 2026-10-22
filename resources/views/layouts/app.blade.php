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

    {{-- Medidas do layout: para mudar o tamanho, altere SÓ aqui --}}
    <style>
        :root {
            --sidebar-w: 18rem;      /* sidebar aberta */
            --sidebar-w-mini: 5rem;  /* sidebar recolhida */
            --header-h: 4rem;
            --sidebar-speed: 400ms;
        }

        html.sidebar-collapsed {
            --sidebar-w: var(--sidebar-w-mini);
        }

        #app-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 50;
            width: var(--sidebar-w);
            transition: width var(--sidebar-speed);
        }

        #app-header {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-w);
            z-index: 40;
            height: var(--header-h);
            transition: left var(--sidebar-speed);
        }

        #app-main {
            padding-top: var(--header-h);
            padding-left: var(--sidebar-w);
            transition: padding-left var(--sidebar-speed);
        }

        /* Os textos acompanham o recolhimento sem desaparecer abruptamente. */
        .sidebar-label {
            max-width: 20rem;
            overflow: hidden;
            opacity: 1;
            transition: max-width var(--sidebar-speed), opacity var(--sidebar-speed), margin-left var(--sidebar-speed);
        }

        html.sidebar-collapsed .sidebar-label {
            max-width: 0;
            margin-left: 0;
            opacity: 0;
        }

        #app-sidebar .sidebar-brand {
            transition: padding var(--sidebar-speed);
        }

        html.sidebar-collapsed #app-sidebar .sidebar-brand {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        #app-sidebar .sidebar-nav-link,
        #app-sidebar .sidebar-account-link {
            transition: gap var(--sidebar-speed), background-color 150ms, color 150ms;
        }

        html.sidebar-collapsed #app-sidebar .sidebar-nav-link,
        html.sidebar-collapsed #app-sidebar .sidebar-account-link {
            gap: 0;
        }

        #app-sidebar p.sidebar-label {
            max-height: 4rem;
            transition: max-width var(--sidebar-speed), max-height var(--sidebar-speed), padding var(--sidebar-speed), opacity var(--sidebar-speed);
        }

        html.sidebar-collapsed #app-sidebar p.sidebar-label {
            max-height: 0;
            padding: 0;
        }
    </style>

    {{-- TallStackUI: precisa vir ACIMA do @vite --}}
    <tallstackui:script />
    <x-ts-toast />
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @livewireStyles
    @vite('resources/css/app.css')
</head>

<body class="bg-slate-50 font-sans text-slate-900 antialiased" x-data="{ menuOpen: false }">
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
