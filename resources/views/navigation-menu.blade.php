<div>
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
                    'active' => request()->routeIs('painel.library') || request()->routeIs('painel.library.show'),
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

        // Fade dos textos da sidebar (um lugar só para ajustar o tempo).
        // Recolhendo: some rápido (100ms). Expandindo: espera 200ms e aparece em 200ms,
        // ou seja, termina junto com a barra de 400ms.
        $labelFade = "sidebarCollapsed ? 'opacity-0 duration-100' : 'opacity-100 delay-200 duration-200'";
    @endphp

    {{-- HEADER --}}
    <header class="border-b border-slate-200 bg-white"
        style="position: fixed; top: 0; right: 0; left: 16rem; z-index: 40; height: 4rem; transition: left 400ms"
        x-bind:style="sidebarCollapsed
            ?
            'position: fixed; top: 0; right: 0; left: 5rem; z-index: 40; height: 4rem; transition: left 400ms' :
            'position: fixed; top: 0; right: 0; left: 16rem; z-index: 40; height: 4rem; transition: left 400ms'">
        <div class="flex h-full items-center justify-between gap-3 px-4 sm:px-6">
            <div class="flex min-w-0 items-center gap-3"></div>

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

    {{-- SIDEBAR: sempre fixa à esquerda, acima do header e até o fim da tela.
         overflow-hidden corta os textos enquanto a barra encolhe. --}}
         <aside id="app-sidebar" class="flex flex-col overflow-hidden border-r border-blue-900 bg-blue-800"
         style="position: fixed; left: 0; top: 0; bottom: 0; z-index: 50; width: 16rem; transition: width 400ms"
         x-bind:style="sidebarCollapsed
             ?
             'position: fixed; left: 0; top: 0; bottom: 0; z-index: 50; width: 5rem; transition: width 400ms' :
             'position: fixed; left: 0; top: 0; bottom: 0; z-index: 50; width: 16rem; transition: width 400ms'"
         aria-label="Navegação principal">

         {{-- Topo da sidebar: sanduíche dentro dela.
              px-5 (20px) + botão de 40px + 20px = 80px: o ícone já fica centralizado na barra recolhida. --}}
         <div class="flex shrink-0 items-center border-b border-blue-700 px-5" style="height: 4rem">
             <button type="button"
                 class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-white"
                 x-on:click="sidebarCollapsed = !sidebarCollapsed" x-bind:aria-expanded="!sidebarCollapsed"
                 aria-label="Recolher ou expandir barra lateral" title="Recolher ou expandir barra lateral">
                 <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                     aria-hidden="true">
                     <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                 </svg>
             </button>

             <div class="ml-3 shrink-0 whitespace-nowrap transition-opacity" x-bind:class="{!! $labelFade !!}">
                 <p class="text-sm font-bold text-white">Biblioteca de Robótica</p>
             </div>
         </div>

         {{-- Navegação (só essa parte rola, se a lista crescer) --}}
         <div class="min-h-0 flex-1 overflow-y-auto overflow-x-hidden px-3 py-6">

             <nav class="space-y-1">
                 @foreach ($navigationItems as $item)
                     <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
                         style="padding-left: 18px; padding-right: 18px" @class([
                             'group flex items-center gap-3 rounded-xl py-3 text-base font-medium transition',
                             'bg-white text-blue-800 shadow-sm' => $item['active'],
                             'text-white hover:bg-blue-700' => !$item['active'],
                         ])
                         @if ($item['active']) aria-current="page" @endif>
                         <svg @class([
                             'size-5 shrink-0 transition-colors',
                             'text-blue-800' => $item['active'],
                             'text-white' => !$item['active'],
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
                                         d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20m6-8a4 4 0 1 0-0-8 4 4 0 0 0 0 8Zm6-7.7a4 4 0 0 1 0 7.4m4 8.3v-1.5a3.5 3.5 0 0 0-2.5-3.35" />
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

                         <span class="shrink-0 whitespace-nowrap transition-opacity"
                             x-bind:class="{!! $labelFade !!}">
                             {{ $item['label'] }}
                         </span>
                     </a>
                 @endforeach
             </nav>

             {{-- Conta --}}
             <div class="mt-7 border-t border-blue-700 pt-5">

                 <p class="whitespace-nowrap px-3 pb-3 text-sm font-bold uppercase tracking-[0.14em] text-blue-200 transition-opacity"
                     x-bind:class="{!! $labelFade !!}">
                     Conta
                 </p>

                 <a href="{{ route('profile.show') }}" title="Meu perfil"
                     style="padding-left: 18px; padding-right: 18px" @class([
                         'group flex items-center gap-3 rounded-xl py-3 text-base font-medium transition',
                         'bg-white text-blue-800 shadow-sm' => request()->routeIs('profile.show'),
                         'text-white hover:bg-blue-700' => !request()->routeIs('profile.show'),
                     ])>
                     <svg @class([
                         'size-5 shrink-0 transition-colors',
                         'text-blue-800' => request()->routeIs('profile.show'),
                         'text-white' => !request()->routeIs('profile.show'),
                     ]) viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.7" aria-hidden="true">
                         <circle cx="12" cy="8" r="3.5" />
                         <path stroke-linecap="round" stroke-linejoin="round" d="M5 21v-1.5a7 7 0 0 1 14 0V21H5Z" />
                     </svg>

                     <span class="shrink-0 whitespace-nowrap transition-opacity"
                         x-bind:class="{!! $labelFade !!}">Meu perfil</span>
                 </a>

                 {{-- Sair: no header ele some em telas pequenas, então fica aqui também --}}
                 <form method="POST" action="{{ route('logout') }}" class="mt-1 sm:hidden">
                     @csrf
                     <button type="submit" title="Sair" style="padding-left: 18px; padding-right: 18px"
                         class="flex w-full items-center gap-3 rounded-xl py-3 text-base font-medium text-white transition hover:bg-blue-700">
                         <svg class="size-5 shrink-0 text-white" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                             <path stroke-linecap="round" stroke-linejoin="round"
                                 d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 17l5-5-5-5M15 12H4" />
                         </svg>
                         <span class="shrink-0 whitespace-nowrap transition-opacity"
                             x-bind:class="{!! $labelFade !!}">Sair</span>
                     </button>
                 </form>
             </div>
         </div>

         {{-- Rodapé (some com fade quando recolhida) --}}
         <div class="shrink-0 border-t border-blue-700 p-4 transition-opacity" x-bind:class="{!! $labelFade !!}">
             <div class="flex items-center gap-2.5 overflow-hidden rounded-xl border border-blue-700 bg-blue-900 p-3">
                 <span
                     class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white text-blue-800 shadow-sm">
                     <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                         aria-hidden="true">
                         <path stroke-linecap="round" stroke-linejoin="round"
                             d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" />
                         <path stroke-linecap="round" d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20M8 7h8M8 10h5" />
                     </svg>
                 </span>
                 <p class="min-w-0 truncate whitespace-nowrap text-sm font-bold text-white">Biblioteca Digital</p>
             </div>
         </div>
     </aside>
</div>
