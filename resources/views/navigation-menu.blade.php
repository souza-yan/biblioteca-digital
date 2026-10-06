@php
    $currentUser = auth()->user();
    $isManagement = $currentUser->isAdmin() || $currentUser->isStaff();
    $navigationItems = [
        [
            'label' => 'Início',
            'route' => 'dashboard',
            'active' => request()->routeIs('dashboard'),
            'icon' => 'home',
        ],
    ];

    if ($isManagement) {
        $navigationItems = [
            ...$navigationItems,
            [
                'label' => 'Materiais',
                'route' => 'painel.materials',
                'active' => request()->routeIs('painel.materials*'),
                'icon' => 'book',
            ],
            [
                'label' => 'Categorias',
                'route' => 'painel.categories',
                'active' => request()->routeIs('painel.categories'),
                'icon' => 'folder',
            ],
            [
                'label' => 'Usuários',
                'route' => 'painel.users',
                'active' => request()->routeIs('painel.users'),
                'icon' => 'users',
            ],
            [
                'label' => 'Atividades',
                'route' => 'painel.activities',
                'active' => request()->routeIs('painel.activities'),
                'icon' => 'activity',
            ],
        ];
    } else {
        $navigationItems = [
            ...$navigationItems,
            [
                'label' => 'Materiais',
                'route' => 'painel.library',
                'active' => request()->routeIs('painel.library')
                    || request()->routeIs('painel.library.show'),
                'icon' => 'book',
            ],
            [
                'label' => 'Categorias',
                'route' => 'painel.library.categories',
                'active' => request()->routeIs('painel.library.categories'),
                'icon' => 'folder',
            ],
            [
                'label' => 'Favoritos',
                'route' => 'painel.favorites',
                'active' => request()->routeIs('painel.favorites'),
                'icon' => 'heart',
            ],
        ];
    }

    $searchRoute = $isManagement ? 'painel.materials' : 'painel.library';
    $roleLabel = $currentUser->isStaff() ? 'Equipe de Informática Educativa' : $currentUser->role->label();
@endphp

<div x-data="{ menuOpen: false }">
    <header class="fixed inset-x-0 top-0 z-40 h-16 border-b border-slate-200 bg-white">
        <div class="flex h-full items-center justify-between gap-3 px-4 sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button"
                    class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-600 lg:hidden"
                    x-on:click="menuOpen = !menuOpen" aria-label="Abrir menu de navegação" aria-controls="app-sidebar"
                    x-bind:aria-expanded="menuOpen">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        aria-hidden="true">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-900 text-white">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20M8 7h8M8 10h5" />
                        </svg>
                    </span>
                    <span class="hidden min-w-0 sm:block">
                        <span class="block truncate text-base font-bold leading-tight text-blue-950">Biblioteca
                            Digital</span>
                        <span class="block truncate text-sm text-slate-500">de Robótica</span>
                    </span>
                </a>

                </span>
            </div>

            <a href="{{ route($searchRoute) }}"
                class="hidden h-11 w-full max-w-md items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 text-left text-base text-slate-500 transition hover:border-blue-300 hover:bg-white sm:flex"
                aria-label="Pesquisar materiais, categorias e autores">
                <svg class="size-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8" aria-hidden="true">
                    <circle cx="10.8" cy="10.8" r="6.8" />
                    <path stroke-linecap="round" d="m16 16 4.5 4.5" />
                </svg>
                <span class="truncate">Pesquisar materiais, categorias, autores...</span>
                <span
                    class="ml-auto hidden rounded border border-slate-200 bg-white px-2 py-1 text-sm text-slate-400 md:inline">Abrir</span>
            </a>

            <div class="flex shrink-0 items-center gap-2 sm:gap-4">
                <a href="{{ route('profile.show') }}"
                    class="flex items-center gap-2 rounded-xl p-1.5 text-left transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-600"
                    aria-label="Abrir perfil de {{ $currentUser->name }}">
                    <span
                        class="flex size-10 items-center justify-center rounded-full bg-blue-100 text-base font-semibold text-blue-900">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($currentUser->name, 0, 1)) }}
                    </span>
                    <span class="hidden max-w-40 sm:block">
                        <span
                            class="block truncate text-base font-semibold text-slate-800">{{ $currentUser->name }}</span>
                        <span class="block truncate text-sm text-slate-500">{{ $roleLabel }}</span>
                    </span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit"
                        class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600">
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div x-cloak x-show="menuOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden"
        x-on:click="menuOpen = false" aria-hidden="true"></div>

    <aside id="app-sidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-200 bg-white pt-16 transition-transform duration-200 lg:z-30 lg:translate-x-0"
        x-bind:class="menuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        aria-label="Navegação principal">
        <div class="flex-1 overflow-y-auto px-3 py-6">
            <p class="px-3 pb-3 text-sm font-bold uppercase tracking-[0.14em] text-slate-400">Menu principal</p>
            <nav class="space-y-1">
                @foreach ($navigationItems as $item)
                    <a href="{{ route($item['route']) }}" @class([
                        'group flex items-center gap-3 rounded-xl px-3 py-3 text-base font-medium transition',
                        'bg-blue-900 text-white shadow-sm' => $item['active'],
                        'text-slate-600 hover:bg-blue-50 hover:text-blue-900' => !$item['active'],
                    ])
                        @if ($item['active']) aria-current="page" @endif x-on:click="menuOpen = false">
                        <svg @class([
                            'size-5 shrink-0',
                            'text-blue-100' => $item['active'],
                            'text-slate-400 group-hover:text-blue-700' => !$item['active'],
                        ]) viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" aria-hidden="true">
                            @switch($item['icon'])
                                @case('home')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z" />
                                @break

                                @case('book')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" />
                                    <path stroke-linecap="round" d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20M8 7h8M8 10h5" />
                                @break

                                @case('folder')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 7.5A1.5 1.5 0 0 1 4.5 6H10l2 2h7.5A1.5 1.5 0 0 1 21 9.5v8a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5v-10Z" />
                                @break

                                @case('users')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20m6-8a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-7.7a4 4 0 0 1 0 7.4m4 8.3v-1.5a3.5 3.5 0 0 0-2.5-3.35" />
                                @break

                                @case('activity')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l2.2-6 4.1 12 2.2-6H21" />
                                @break

                                @case('heart')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20.8 8.8c0 4.2-8.8 10.2-8.8 10.2S3.2 13 3.2 8.8A4.3 4.3 0 0 1 12 6.7a4.3 4.3 0 0 1 8.8 2.1Z" />
                                @break
                            @endswitch
                        </svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-7 border-t border-slate-100 pt-5">
                <p class="px-3 pb-3 text-sm font-bold uppercase tracking-[0.14em] text-slate-400">Conta</p>
                <a href="{{ route('profile.show') }}" @class([
                    'flex items-center gap-3 rounded-xl px-3 py-3 text-base font-medium transition',
                    'bg-blue-50 text-blue-900' => request()->routeIs('profile.show'),
                    'text-slate-600 hover:bg-blue-50 hover:text-blue-900' => !request()->routeIs(
                        'profile.show'),
                ])>
                    <svg class="size-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" aria-hidden="true">
                        <circle cx="12" cy="8" r="3.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 21v-1.5a7 7 0 0 1 14 0V21H5Z" />
                    </svg>
                    Meu perfil
                </a>
            </div>
        </div>

        <div class="border-t border-slate-100 p-4">
            <div class="overflow-hidden rounded-xl border border-blue-100 bg-blue-50 p-3">
                <div class="flex items-center gap-2.5">
                    <span
                        class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white text-blue-900 shadow-sm">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" />
                            <path stroke-linecap="round" d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20M8 7h8M8 10h5" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-blue-950">Biblioteca Digital</p>
                        <p class="text-sm leading-5 text-blue-800">Educação, tecnologia e inovação para um futuro
                            melhor.</p>
                    </div>
                </div>
                <div class="mt-3 h-1.5 rounded-full bg-blue-100">
                    <div class="h-full w-1/3 rounded-full bg-amber-400"></div>
                </div>
            </div>
        </div>
    </aside>
</div>
