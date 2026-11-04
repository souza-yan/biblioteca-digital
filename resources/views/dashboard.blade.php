<x-app-layout>
    @php
        $currentUser = auth()->user();
        $isStaff = $currentUser->isStaff();
        $staffSchool = $isStaff ? $currentUser->school() : null;
        $libraryRoute = $isTeacher ? 'painel.library.categories' : 'painel.materials';
        $totalMaterials = $isTeacher ? null : $statusCounts->sum();
        $publishedMaterialsCount = $isTeacher ? null : $statusCounts->get('Publicado', 0);
        $maxDownloads = $isTeacher ? 0 : max(1, (int) $topDownloadedMaterials->max('downloads_count'));
    @endphp

    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">

        @if ($isTeacher)

            <section class="overflow-hidden rounded-3xl bg-blue-800 px-5 py-7 text-white shadow-sm sm:px-8 sm:py-9" aria-labelledby="teacher-welcome-title">
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-200">
                    Biblioteca digital de robótica
                </p>
                <div class="mt-3 max-w-2xl">
                    <h1 id="teacher-welcome-title" class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Encontre recursos para suas aulas
                    </h1>
                    <p class="mt-3 text-base text-blue-100 sm:text-lg">
                        Explore projetos, atividades e materiais de robótica educacional.
                    </p>
                    @if ($teacherSchool)
                        <p class="mt-3 text-sm font-medium text-blue-200">Professor(a) · {{ $teacherSchool->name }}</p>
                    @endif
                </div>

                <form action="{{ route('painel.library') }}" method="GET" class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <label for="dashboard-material-search" class="sr-only">Pesquisar materiais</label>
                    <div class="relative min-w-0 flex-1">
                        <x-ts-icon icon="magnifying-glass" outline class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-slate-400" />
                        <input id="dashboard-material-search" name="q" type="search" placeholder="Busque por título ou autor"
                            class="h-12 w-full rounded-xl border-0 bg-white py-3 pl-12 pr-4 text-slate-900 shadow-sm placeholder:text-slate-400 focus:ring-2 focus:ring-blue-200">
                    </div>
                    <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">
                        Buscar materiais
                        <x-ts-icon icon="arrow-right" outline class="size-5" />
                    </button>
                </form>
            </section>

            @php
                $categorySlides = $categories->chunk(3);
            @endphp
            <section aria-labelledby="categories-title" x-data="{ activeCategorySlide: 0, totalCategorySlides: {{ $categorySlides->count() }} }">
                <div class="flex items-end justify-between gap-4">
                    <h2 id="categories-title" class="text-2xl font-bold tracking-tight text-slate-900">Explore por categoria</h2>
                    <div class="flex items-center gap-2">
                        @if ($categories->count() > 3)
                            <button type="button" x-on:click="activeCategorySlide = (activeCategorySlide - 1 + totalCategorySlides) % totalCategorySlides"
                                aria-label="Ver categorias anteriores" title="Ver categorias anteriores"
                                class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-blue-800 transition hover:border-blue-200 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-600">
                                <x-ts-icon icon="chevron-left" outline class="size-5" />
                            </button>
                            <button type="button" x-on:click="activeCategorySlide = (activeCategorySlide + 1) % totalCategorySlides"
                                aria-label="Ver próximas categorias" title="Ver próximas categorias"
                                class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-blue-800 transition hover:border-blue-200 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-600">
                                <x-ts-icon icon="chevron-right" outline class="size-5" />
                            </button>
                        @endif
                        <a href="{{ route('painel.library.categories') }}" class="shrink-0 text-sm font-semibold text-blue-800 hover:underline">Ver todas</a>
                    </div>
                </div>

                <div class="mt-5">
                    @if ($categorySlides->isEmpty())
                        <p class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-8 text-sm text-slate-500">Ainda não há categorias com materiais publicados.</p>
                    @else
                        @foreach ($categorySlides as $slideIndex => $categorySlide)
                            <div x-show="activeCategorySlide === {{ $slideIndex }}"
                                x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none"
                                x-transition:enter-start="translate-x-4 opacity-0"
                                x-transition:enter-end="translate-x-0 opacity-100"
                                x-transition:leave="transition ease-in duration-200 motion-reduce:transition-none"
                                x-transition:leave-start="translate-x-0 opacity-100"
                                x-transition:leave-end="-translate-x-4 opacity-0"
                                @if (! $loop->first) x-cloak @endif class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($categorySlide as $category)
                                    @php
                                        $categoryName = \Illuminate\Support\Str::lower($category->name);
                                        $categoryIcon = match (true) {
                                            str_contains($categoryName, 'arduino') => 'cpu-chip',
                                            str_contains($categoryName, 'eletr') => 'bolt',
                                            str_contains($categoryName, 'lego') => 'cube',
                                            str_contains($categoryName, 'program') => 'command-line',
                                            default => 'folder-open',
                                        };
                                    @endphp
                                    <a href="{{ route('painel.library', ['categoryFilter' => $category->getKey()]) }}" class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/50">
                                        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                                            <x-ts-icon :icon="$categoryIcon" outline class="size-6" />
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate font-semibold text-slate-800 group-hover:text-blue-800">{{ $category->name }}</span>
                                            <span class="mt-0.5 block text-sm text-slate-500">{{ $category->published_materials_count }} {{ $category->published_materials_count === 1 ? 'material publicado' : 'materiais publicados' }}</span>
                                        </span>
                                        <x-ts-icon icon="chevron-right" outline class="size-5 shrink-0 text-slate-400 group-hover:text-blue-700" />
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>

            <div class="grid min-w-0 grid-cols-1 gap-8 xl:grid-cols-[minmax(0,3fr)_minmax(19rem,2fr)]">
                <section class="min-w-0" aria-labelledby="recent-materials-title">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <h2 id="recent-materials-title" class="text-2xl font-bold tracking-tight text-slate-900">Materiais publicados recentemente</h2>
                        </div>
                        <a href="{{ route('painel.library') }}" class="shrink-0 text-sm font-semibold text-blue-800 hover:underline">Ver materiais</a>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($publishedMaterials as $material)
                            <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                                <a href="{{ route('painel.library.show', $material) }}" class="block p-3" aria-label="Abrir {{ $material->title }}">
                                    <x-materials.pdf-cover :material="$material" :version="$material->currentVersion" :alt="'Capa do material '.$material->title" fit="contain" class="rounded-xl" />
                                </a>
                                <div class="p-4 pt-1">
                                    <p class="truncate text-sm font-medium text-blue-700">{{ $material->category->name }}</p>
                                    <a href="{{ route('painel.library.show', $material) }}" class="mt-1 block truncate text-base font-bold text-slate-900 hover:text-blue-800">{{ $material->title }}</a>
                                    <div class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500">
                                        <span>{{ $material->published_at?->format('d/m/Y') ?? 'Data não informada' }}</span>
                                        <span class="rounded-full bg-slate-100 px-2 py-1 font-semibold uppercase tracking-wide text-slate-600">{{ $material->type }}</span>
                                    </div>
                                </div>
                                <div class="absolute right-5 top-5">
                                    @livewire('materials.favorite-button', ['material' => $material, 'isFavorited' => $material->is_favorited], key('dashboard-favorite-'.$material->getKey()))
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center sm:col-span-2 lg:col-span-3">
                                <x-ts-icon icon="document-text" outline class="mx-auto size-10 text-slate-400" />
                                <p class="mt-3 font-semibold text-slate-700">Ainda não há materiais publicados.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="recently-accessed-title">
                    <div>
                        <h2 id="recently-accessed-title" class="text-xl font-bold text-slate-900">Continue de onde parou</h2>
                    </div>
                    <div class="mt-5 space-y-3">
                        @forelse ($recentlyAccessedMaterials as $material)
                            <article class="flex min-w-0 items-center gap-3 rounded-xl border border-slate-100 p-3 transition hover:border-blue-100 hover:bg-blue-50/40">
                                <x-materials.pdf-cover :material="$material" :version="$material->currentVersion" :alt="'Miniatura de '.$material->title" fit="contain" class="h-20 w-14 shrink-0 !aspect-auto rounded-lg" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-slate-800">{{ $material->title }}</p>
                                    <p class="mt-1 truncate text-sm text-slate-500">{{ $material->category->name }} · {{ $material->type }}</p>
                                    <a href="{{ route('painel.library.show', $material) }}" class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-blue-800 hover:underline">
                                        Abrir material
                                        <x-ts-icon icon="arrow-right" outline class="size-4" />
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Os materiais acessados ou baixados aparecerão aqui.</div>
                        @endforelse
                    </div>
                </section>
            </div>
        @elseif ($isStaff)
            <header class="rounded-2xl bg-blue-800 px-5 py-6 text-white sm:px-7 sm:py-7">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-200">Gestão escolar</p>
                        <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">{{ $staffSchool?->name }}</h1>
                        <p class="mt-2 max-w-2xl text-base text-blue-100">
                            Acompanhe os professores da escola e consulte os conteúdos disponíveis na biblioteca.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('painel.users') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-3 text-sm font-semibold text-blue-800 transition hover:bg-blue-50">
                            Gerenciar professores
                        </a>
                        <a href="{{ route('painel.materials') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-blue-300 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                            Consultar materiais
                        </a>
                    </div>
                </div>
            </header>

            <section aria-label="Resumo da escola" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white p-5">
                    <p class="text-sm font-semibold text-slate-500">Professores vinculados</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($activeUsersCount, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-500">vínculos ativos na escola</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5">
                    <p class="text-sm font-semibold text-slate-500">Materiais publicados</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($publishedMaterialsCount, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-500">disponíveis para consulta</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5">
                    <p class="text-sm font-semibold text-slate-500">Downloads da escola</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($recentDownloadsCount, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-500">nos últimos 30 dias</p>
                </article>
            </section>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="staff-materials-title">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 id="staff-materials-title" class="text-xl font-bold text-slate-900">Materiais mais acessados</h2>
                            <p class="mt-1 text-sm text-slate-500">Downloads feitos por pessoas atualmente vinculadas à escola.</p>
                        </div>
                        <a href="{{ route('painel.materials') }}" class="shrink-0 text-sm font-semibold text-blue-800 hover:underline">Ver materiais</a>
                    </div>
                    <div class="mt-5 divide-y divide-slate-100">
                        @forelse ($topDownloadedMaterials as $material)
                            <a href="{{ route('painel.materials.show', $material) }}" class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold text-slate-800">{{ $material->title }}</span>
                                    <span class="mt-1 block truncate text-sm text-slate-500">{{ $material->category->name }}</span>
                                </span>
                                <span class="shrink-0 text-sm font-semibold text-slate-600">{{ number_format($material->downloads_count, 0, ',', '.') }}</span>
                            </a>
                        @empty
                            <p class="py-6 text-sm text-slate-500">Ainda não há downloads registrados para esta escola.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="staff-actions-title">
                    <h2 id="staff-actions-title" class="text-xl font-bold text-slate-900">Acesso rápido</h2>
                    <p class="mt-1 text-sm text-slate-500">Tarefas disponíveis para a gestão de {{ $staffSchool?->name }}.</p>
                    <div class="mt-5 grid gap-3">
                        <a href="{{ route('painel.users') }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-4 font-semibold text-slate-800 transition hover:border-blue-200 hover:bg-blue-50">
                            Professores e vínculos <span class="text-blue-800" aria-hidden="true">›</span>
                        </a>
                        <a href="{{ route('painel.materials') }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-4 font-semibold text-slate-800 transition hover:border-blue-200 hover:bg-blue-50">
                            Biblioteca de materiais <span class="text-blue-800" aria-hidden="true">›</span>
                        </a>
                        <a href="{{ route('profile.show') }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-4 font-semibold text-slate-800 transition hover:border-blue-200 hover:bg-blue-50">
                            Dados da conta <span class="text-blue-800" aria-hidden="true">›</span>
                        </a>
                    </div>
                </section>
            </div>
        @else
            {{-- ======================================== --}}
            {{-- DASHBOARD ADMIN / GESTÃO                 --}}
            {{-- ORIGINAL - NÃO ALTERADO                 --}}
            {{-- ======================================== --}}

            <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">
                        Visão Geral
                    </h1>
                </div>

                <a href="{{ route('painel.materials') }}"
                    class="inline-flex shrink-0 items-center gap-2 text-base font-semibold text-blue-800 hover:text-blue-800">
                    Ver materiais
                    <span aria-hidden="true">›</span>
                </a>
            </header>

            <section aria-label="Resumo administrativo" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <article class="flex items-center gap-4 rounded-2xl border border-blue-100 bg-white p-5 sm:p-6">
                    <span
                        class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 3.75h8.5L19 8.25v12H6a2 2 0 0 1-2-2v-12a2 2 0 0 1 2-2Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 4v5h5M8 13h8m-8 4h8" />
                        </svg>
                    </span>

                    <div class="min-w-0">
                        <p class="text-lg font-semibold text-blue-800">
                            Total de materiais
                        </p>

                        <p class="mt-1 text-4xl font-bold tracking-tight text-blue-800 sm:text-5xl">
                            {{ number_format($totalMaterials, 0, ',', '.') }}
                        </p>

                        <p class="mt-1 text-base text-slate-500">
                            cadastros no sistema
                        </p>
                    </div>
                </article>

                <article class="flex items-center gap-4 rounded-2xl border border-violet-100 bg-white p-5 sm:p-6">
                    <span
                        class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                            <circle cx="7" cy="7" r="1" />
                            <circle cx="7" cy="12" r="1" />
                            <circle cx="7" cy="17" r="1" />
                        </svg>
                    </span>

                    <div class="min-w-0">
                        <p class="text-lg font-semibold text-blue-800">
                            Total de categorias
                        </p>

                        <p class="mt-1 text-4xl font-bold tracking-tight text-blue-800 sm:text-5xl">
                            {{ number_format($categoryCounts['total'], 0, ',', '.') }}
                        </p>

                        <p class="mt-1 text-base text-slate-500">
                            categorias cadastradas
                        </p>
                    </div>
                </article>

                <article class="flex items-center gap-4 rounded-2xl border border-violet-100 bg-white p-5 sm:p-6">
                    <span
                        class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" aria-hidden="true">
                            <circle cx="9" cy="8" r="3.5" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.5 20v-1.3a4.7 4.7 0 0 1 4.7-4.7h3.6a4.7 4.7 0 0 1 4.7 4.7V20h-13Z" />
                            <path stroke-linecap="round"
                                d="M16 5a3.5 3.5 0 0 1 0 6.8m2 2.5a4.7 4.7 0 0 1 3.5 4.5V20" />
                        </svg>
                    </span>

                    <div class="min-w-0">
                        <p class="text-lg font-semibold text-blue-800">
                            Usuários ativos
                        </p>

                        <p class="mt-1 text-4xl font-bold tracking-tight text-blue-800 sm:text-5xl">
                            {{ number_format($activeUsersCount, 0, ',', '.') }}
                        </p>

                        <p class="mt-1 text-base text-slate-500">
                            com status ativo
                        </p>
                    </div>
                </article>

                <article class="flex items-center gap-4 rounded-2xl border border-amber-100 bg-white p-5 sm:p-6">
                    <span
                        class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v3h16v-3" />
                        </svg>
                    </span>

                    <div class="min-w-0">
                        <p class="text-lg font-semibold text-blue-800">
                            Downloads
                        </p>

                        <p class="mt-1 text-4xl font-bold tracking-tight text-blue-800 sm:text-5xl">
                            {{ number_format($recentDownloadsCount, 0, ',', '.') }}
                        </p>

                        <p class="mt-1 text-base text-slate-500">
                            nos últimos 30 dias
                        </p>
                    </div>
                </article>
            </section>
        @endif

        @if (! $isTeacher && ! $isStaff)
            <div class="grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-2">
                <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 xl:p-8"
                    aria-labelledby="materials-status-title">

                    <h2 id="materials-status-title" class="text-2xl font-bold text-slate-900">
                        Materiais por status
                    </h2>

                    <p class="mt-1 text-base text-slate-500">
                        Status cadastral do acervo da biblioteca.
                    </p>

                    <div class="mt-6 space-y-5">
                        @foreach ($statusCounts as $status => $total)
                            @php
                                $barColor = match ($status) {
                                    'Publicado' => 'bg-emerald-500',
                                    'Arquivado' => 'bg-slate-400',
                                    default => 'bg-blue-800',
                                };

                                $percentage =
                                    $totalMaterials > 0 ? min(100, (int) round(($total / $totalMaterials) * 100)) : 0;
                            @endphp

                            <div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="text-base font-semibold text-slate-800">
                                        {{ $status }}
                                    </span>

                                    <span class="shrink-0 text-base font-semibold text-slate-700">
                                        {{ number_format($total, 0, ',', '.') }}
                                        / {{ number_format($totalMaterials, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-100" role="progressbar"
                                    aria-label="Proporção de materiais {{ mb_strtolower($status) }}"
                                    aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">

                                    <div class="h-full rounded-full {{ $barColor }}"
                                        style="width: {{ $percentage }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 xl:p-8">
                    <h2 class="text-2xl font-bold text-slate-900">
                        Categorias
                    </h2>

                    <p class="mt-1 text-base text-slate-500">
                        Organização dos temas cadastrados no acervo.
                    </p>

                    <div class="mt-6 space-y-5">
                        @foreach ([['label' => 'Total', 'value' => $categoryCounts['total'], 'color' => 'bg-blue-800'], ['label' => 'Ativas', 'value' => $categoryCounts['active'], 'color' => 'bg-emerald-500'], ['label' => 'Inativas', 'value' => $categoryCounts['inactive'], 'color' => 'bg-slate-400']] as $categoryStat)
                            @php
                                $categoryShare =
                                    $categoryCounts['total'] > 0
                                        ? min(
                                            100,
                                            (int) round(($categoryStat['value'] / $categoryCounts['total']) * 100),
                                        )
                                        : 0;
                            @endphp

                            <div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="text-base font-semibold text-slate-800">
                                        {{ $categoryStat['label'] }}
                                    </span>

                                    <span class="text-base font-semibold text-slate-700">
                                        {{ number_format($categoryStat['value'], 0, ',', '.') }}

                                        @if ($categoryStat['label'] !== 'Total')
                                            / {{ number_format($categoryCounts['total'], 0, ',', '.') }}
                                        @endif
                                    </span>
                                </div>

                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $categoryStat['color'] }}"
                                        style="width: {{ $categoryStat['label'] === 'Total' && $categoryCounts['total'] > 0 ? 100 : $categoryShare }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <a href="{{ route('painel.categories') }}"
                        class="mt-6 inline-flex text-base font-semibold text-blue-800 hover:text-blue-800">
                        Gerenciar categorias
                        <span class="ml-1" aria-hidden="true">›</span>
                    </a>
                </article>
            </div>
        @endif

        @if (! $isTeacher && ! $isStaff)
            <div class="grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-2">

                <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 xl:p-8"
                    aria-labelledby="top-downloads-title">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h2 id="top-downloads-title" class="text-2xl font-bold text-slate-900">
                                Materiais mais baixados
                            </h2>

                            <p class="mt-1 text-base text-slate-500">
                                Distribuição dos downloads no período selecionado.
                            </p>
                        </div>

                        <a href="{{ route('painel.materials.most-downloaded', ['downloadPeriod' => $downloadPeriod]) }}"
                            class="shrink-0 text-base font-semibold text-blue-800 hover:text-blue-800">
                            Ver todas
                            <span aria-hidden="true">›</span>
                        </a>

                    </div>

                    <div class="mt-6 space-y-5">
                        @forelse ($topDownloadedMaterials as $material)
                            @php
                                $downloadShare = min(
                                    100,
                                    (int) round(($material->downloads_count / $maxDownloads) * 100),
                                );
                            @endphp

                            <a href="{{ route('painel.materials.show', ['material' => $material->getKey()]) }}"
                                class="block min-w-0">

                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="min-w-0 truncate text-base font-semibold text-slate-800">
                                        {{ $material->title }}
                                    </span>

                                    <span class="shrink-0 text-base font-semibold text-slate-700">
                                        {{ number_format($material->downloads_count, 0, ',', '.') }}
                                    </span>
                                </div>

                                <p class="mt-0.5 truncate text-base text-slate-500">
                                    {{ $material->category->name }}
                                </p>

                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-blue-800" style="width: {{ $downloadShare }}%">
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="py-8 text-center text-base text-slate-500">
                                Nenhum material baixado neste período.
                            </p>
                        @endforelse
                    </div>
                </section>

                <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 xl:p-8"
                    aria-labelledby="activities-title">

                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 id="activities-title" class="text-2xl font-bold text-slate-900">
                                Atividades recentes
                            </h2>

                            <p class="mt-1 text-base text-slate-500">
                                Últimos registros no sistema.
                            </p>
                        </div>

                        <a href="{{ route('painel.activities') }}"
                            class="shrink-0 text-base font-semibold text-blue-800 hover:text-blue-800">
                            Ver todas
                            <span aria-hidden="true">›</span>
                        </a>
                    </div>

                    <div class="mt-6 divide-y divide-slate-100">
                        @forelse ($recentActivities as $activity)
                            <article class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="text-base font-semibold leading-6 text-slate-800">
                                        {{ $activity->description }}
                                    </p>

                                    <p class="mt-1 truncate text-base text-slate-500">
                                        {{ $activity->user->name }}

                                        @if ($activity->material)
                                            <span aria-hidden="true">·</span>
                                            {{ $activity->material->title }}
                                        @endif
                                    </p>
                                </div>

                                <time class="shrink-0 text-base text-slate-400"
                                    datetime="{{ $activity->created_at->toIso8601String() }}">
                                    {{ $activity->created_at->format('d/m H:i') }}
                                </time>
                            </article>
                        @empty
                            <p class="py-8 text-center text-base text-slate-500">
                                Nenhuma atividade registrada.
                            </p>
                        @endforelse
                    </div>
                </article>

            </div>
        @endif
    </div>
</x-app-layout>
