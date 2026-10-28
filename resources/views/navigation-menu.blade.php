<div>
    @php
        $currentUser = auth()->user();
        $navigationItems = [
            [
                'label' => 'Início',
                'route' => 'dashboard',
                'active' => request()->routeIs('dashboard'),
                'icon' => 'home',
            ],
        ];

        if ($currentUser->isAdmin()) {
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
                    'label' => 'Professores',
                    'route' => 'painel.users',
                    'active' => request()->routeIs('painel.users'),
                    'icon' => 'users',
                ],
                [
                    'label' => 'Escolas',
                    'route' => 'painel.schools',
                    'active' => request()->routeIs('painel.schools'),
                    'icon' => 'school',
                ],
                [
                    'label' => 'Atividades',
                    'route' => 'painel.activities',
                    'active' => request()->routeIs('painel.activities'),
                    'icon' => 'activity',
                ],
            ];
        } elseif ($currentUser->isStaff()) {
            $staffSchool = $currentUser->school();
            $navigationItems = [
                ...$navigationItems,
                [
                    'label' => 'Meu histórico',
                    'route' => 'painel.history',
                    'active' => request()->routeIs('painel.history'),
                    'icon' => 'history',
                ],
                [
                    'label' => 'Professores',
                    'route' => 'painel.users',
                    'active' => request()->routeIs('painel.users'),
                    'icon' => 'users',
                ],
                [
                    'label' => 'Materiais',
                    'route' => 'painel.library.categories',
                    'active' => request()->routeIs('painel.library*')
                        || request()->routeIs('painel.materials*')
                        || request()->routeIs('painel.categories'),
                    'icon' => 'book',
                ],
            ];
        } else {
            $staffSchool = null;
            $navigationItems = [
                ...$navigationItems,
                [
                    'label' => 'Meu histórico',
                    'route' => 'painel.history',
                    'active' => request()->routeIs('painel.history'),
                    'icon' => 'history',
                ],
                [
                    'label' => 'Materiais',
                    'route' => 'painel.library.categories',
                    'active' => request()->routeIs('painel.library', 'painel.library.*'),
                    'icon' => 'folder',
                ],
                [
                    'label' => 'Favoritos',
                    'route' => 'painel.favorites',
                    'active' => request()->routeIs('painel.favorites'),
                    'icon' => 'bookmark',
                ],
            ];
        }

        if ($currentUser->isAdmin()) {
            $staffSchool = null;
        }

        $roleLabel = $currentUser->isStaff() ? 'Gestão escolar' : $currentUser->role->label();
    @endphp

    {{-- Cabeçalho global: abre o drawer no mobile e mantém os atalhos da conta visíveis. --}}
    <header id="app-header" class="border-b border-slate-200 bg-white">
        <div class="flex h-full items-center justify-between gap-3 px-4 sm:px-6">
            <div class="flex min-w-0 items-center gap-3 lg:pl-6">
                <button type="button"
                    class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl text-blue-900 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-600 lg:hidden"
                    @click="menuOpen = true" :aria-expanded="menuOpen.toString()" aria-controls="app-sidebar"
                    aria-label="Abrir menu principal" title="Abrir menu principal">
                    <x-ts-icon icon="bars-3" outline class="size-7" />
                </button>
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-2 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-blue-600"
                    aria-label="Ir para a página inicial">
                    <img src="{{ asset('images/logo_caraguatatuba.svg') }}" alt="Logo Prefeitura de Caraguatatuba"
                        class="h-10 w-auto">
                </a>
            </div>
            <div class="flex shrink-0 items-center gap-2 sm:gap-4">
                <a href="{{ route('profile.show') }}"
                    class="flex items-center gap-2 rounded-xl p-1.5 text-left transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-600"
                    aria-label="Abrir perfil de {{ $currentUser->name }}">

                    @if ($currentUser->profile_photo_path)
                        <img src="{{ $currentUser->profile_photo_url }}" alt="{{ $currentUser->name }}"
                            class="size-10 shrink-0 rounded-full object-cover">
                    @else
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-base font-semibold text-blue-900">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($currentUser->name, 0, 1)) }}
                        </span>
                    @endif

                    <span class="hidden min-w-0 max-w-72 sm:block">
                        <span
                            class="block truncate text-base font-semibold text-slate-800">{{ $currentUser->name }}</span>
                        <span class="block truncate text-sm text-slate-500"
                            @if ($staffSchool) title="{{ $roleLabel }} · {{ $staffSchool->name }}" @endif>
                            {{ $roleLabel }}@if ($staffSchool)<span class="px-1" aria-hidden="true">·</span><span class="font-medium text-blue-800">{{ $staffSchool->name }}</span>@endif
                        </span>
                    </span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit"
                        aria-label="Sair" title="Sair"
                        class="inline-flex size-12 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <x-ts-icon icon="arrow-right-start-on-rectangle" outline class="size-7" />
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Fundo clicável do drawer mobile. --}}
    <div x-cloak x-show="menuOpen" x-transition.opacity @click="closeMobileMenu()"
        class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" aria-hidden="true"></div>

    {{-- Navegação principal: drawer no mobile e sidebar recolhível no desktop. --}}
    <aside id="app-sidebar" :class="{ 'mobile-menu-open': menuOpen }"
        class="flex flex-col overflow-hidden border-r border-blue-900 bg-blue-800"
        aria-label="Navegação principal" x-trap.inert.noscroll="menuOpen">

        <div class="sidebar-brand flex shrink-0 items-center border-b border-blue-700 px-5" style="height: 4rem">
            <button type="button"
                class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-white"
                @click="window.innerWidth < 1024 ? closeMobileMenu() : toggleDesktopSidebar()"
                :aria-label="window.innerWidth < 1024 ? 'Fechar menu principal' : 'Recolher ou expandir barra lateral'"
                aria-controls="app-sidebar" title="Recolher, expandir ou fechar o menu">
                <x-ts-icon icon="x-mark" outline class="size-7 lg:hidden" />
                <x-ts-icon icon="bars-3" outline class="hidden size-7 lg:block" />
            </button>
            <div class="sidebar-brand-title sidebar-label ml-3 shrink-0 whitespace-nowrap">
                <p class="text-base font-bold text-white">Biblioteca de Robótica</p>
            </div>
        </div>

        {{-- Navegação (só essa parte rola, se a lista crescer) --}}
        <div class="min-h-0 flex-1 overflow-y-auto overflow-x-hidden px-3 py-6">

            <nav class="space-y-1">
                @foreach ($navigationItems as $item)
                    <a href="{{ route($item['route']) }}" @click="closeMobileMenu()" title="{{ $item['label'] }}" aria-label="{{ $item['label'] }}"
                        style="padding-left: 18px; padding-right: 18px" @class([
                            'sidebar-nav-link group flex items-center gap-3 rounded-xl py-3 text-base font-medium transition',
                            'bg-white text-blue-800 shadow-sm' => $item['active'],
                            'text-white hover:bg-blue-700' => !$item['active'],
                        ])
                        @if ($item['active']) aria-current="page" @endif>
                        <x-navigation-icon :icon="$item['icon']" :active="$item['active']" />

                        <span class="sidebar-label shrink-0 whitespace-nowrap">
                            {{ $item['label'] }}
                        </span>
                    </a>
                @endforeach
            </nav>

            {{-- Conta --}}
            <div class="mt-7 border-t border-blue-700 pt-5">

                <p
                    class="sidebar-label whitespace-nowrap px-3 pb-3 text-sm font-bold uppercase tracking-[0.14em] text-blue-200">
                    Conta
                </p>

                <a href="{{ route('profile.show') }}" @click="closeMobileMenu()" title="Meu perfil" aria-label="Meu perfil"
                    style="padding-left: 18px; padding-right: 18px" @class([
                        'sidebar-account-link group flex items-center gap-3 rounded-xl py-3 text-base font-medium transition',
                        'bg-white text-blue-800 shadow-sm' => request()->routeIs('profile.show'),
                        'text-white hover:bg-blue-700' => !request()->routeIs('profile.show'),
                    ])>
                    <x-navigation-icon icon="profile" :active="request()->routeIs('profile.show')" />

                    <span class="sidebar-label shrink-0 whitespace-nowrap">Meu perfil</span>
                </a>

                {{-- Sair: no header ele some em telas pequenas, então fica aqui também --}}
                <form method="POST" action="{{ route('logout') }}" class="mt-1 sm:hidden">
                    @csrf
                    <button type="submit" title="Sair" aria-label="Sair"
                        class="flex w-full items-center justify-center rounded-xl py-3 text-white transition hover:bg-blue-700">
                        <x-ts-icon icon="arrow-right-start-on-rectangle" outline class="size-7" />
                    </button>
                </form>
            </div>
        </div>
    </aside>
</div>
