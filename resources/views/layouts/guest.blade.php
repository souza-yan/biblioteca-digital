<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <tallstackui:script />

    <!-- Styles -->
    @livewireStyles

    @vite('resources/css/app.css')
</head>
<body x-data="{ appLoading: true }" x-init="$nextTick(() => appLoading = false)">
    <div x-show="appLoading" x-transition.opacity.duration.150ms>
        <x-skeleton.page class="!inset-0 !z-50" />
    </div>

    <div class="font-sans text-gray-900 antialiased">
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>
