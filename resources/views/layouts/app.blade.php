<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Biblioteca Digital de Robótica') }}</title>

        {{-- Dependencia dos Scripts da library --}}
        <tallstackui:script />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="bg-slate-50 font-sans antialiased text-slate-900">
        <x-banner />

        <div class="min-h-screen">
            @livewire('navigation-menu')

            <div class="min-h-screen pt-16 lg:pl-64">
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
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
