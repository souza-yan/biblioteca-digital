<x-app-layout>
    @php
        $currentUser = auth()->user();
        $libraryRoute = $isTeacher ? 'painel.library' : 'painel.materials';
        $totalMaterials = $isTeacher ? null : $statusCounts->sum();
        $publishedMaterialsCount = $isTeacher ? null : $statusCounts->get('Publicado', 0);
        $maxDownloads = $isTeacher ? 0 : max(1, (int) $topDownloadedMaterials->max('downloads_count'));
    @endphp

    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">

        @if ($isTeacher)

            {{-- Visão Geral --}}
            <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0 space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Visão Geral</h1>
                    <p class="text-base text-slate-500 sm:text-lg">
                        Acompanhe os principais indicadores da sua biblioteca digital.
                    </p>
                </div>

                <a href="{{ route('painel.library') }}"
                    class="inline-flex shrink-0 items-center gap-2 text-base font-semibold text-blue-800 hover:text-blue-800">
                    Ver biblioteca
                    <span aria-hidden="true">›</span>
                </a>
            </header>

            {{-- Acesso rápido --}}
            <section aria-labelledby="quick-links-title">
                <div class="mb-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-800">
                        Acesso rápido
                    </p>

                    <h2 id="quick-links-title" class="mt-1 text-xl font-bold text-slate-900">
                        O que você precisa fazer?
                    </h2>
                </div>

                @php
                    $quickLinks = [
                        [
                            'href' => route('painel.library'),
                            'title' => 'Pesquisar materiais',
                            'text' => 'Encontre por título ou autor',
                            'box' => 'bg-blue-50 text-blue-600',
                            'border' => 'border-blue-100',
                            'icon' =>
                                '<circle cx="10.8" cy="10.8" r="6.8" /><path stroke-linecap="round" d="m16 16 4.5 4.5" />',
                        ],
                        [
                            'href' => route('painel.library'),
                            'title' => 'Explorar categorias',
                            'text' => 'Filtre a biblioteca por categoria',
                            'box' => 'bg-violet-10 text-violet-600',
                            'border' => 'border-violet-600',
                            'icon' =>
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A1.5 1.5 0 0 1 4.5 6H10l2 2h7.5A1.5 1.5 0 0 1 21 9.5v8a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5v-10Z" />',
                        ],
                        [
                            'href' => route('painel.favorites'),
                            'title' => 'Meus favoritos',
                            'text' => 'Acesse seus materiais salvos',
                            'box' => 'bg-rose-50 text-rose-600',
                            'border' => 'border-rose-100',
                            'icon' =>
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M20.8 8.8c0 4.2-8.8 10.2-8.8 10.2S3.2 13 3.2 8.8A4.3 4.3 0 0 1 12 6.7a4.3 4.3 0 0 1 8.8 2.1Z" />',
                        ],
                        [
                            'href' => route('profile.show'),
                            'title' => 'Meu perfil',
                            'text' => 'Consulte seus dados da conta',
                            'box' => 'bg-amber-50 text-amber-600',
                            'border' => 'border-amber-100',
                            'icon' =>
                                '<circle cx="12" cy="8" r="3.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M5 21v-1.5a7 7 0 0 1 14 0V21H5Z" />',
                        ],
                    ];
                @endphp

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($quickLinks as $link)
                        <a href="{{ $link['href'] }}"
                            class="group flex items-center gap-4 rounded-2xl border {{ $link['border'] }} bg-white p-5 transition hover:bg-blue-50/40 sm:p-6">
                            <span
                                class="flex size-14 shrink-0 items-center justify-center rounded-2xl {{ $link['box'] }}">
                                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7" aria-hidden="true">
                                    {!! $link['icon'] !!}
                                </svg>
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="block text-lg font-semibold text-blue-800">
                                    {{ $link['title'] }}
                                </span>

                                <span class="mt-1 block text-base text-slate-500">
                                    {{ $link['text'] }}
                                </span>
                            </span>

                            <span class="shrink-0 text-lg text-blue-600 transition group-hover:translate-x-0.5"
                                aria-hidden="true">
                                →
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>


            <div class="grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-12">

                {{-- Materiais mais baixados/acessados --}}
                <section class="w-full min-w-0 space-y-4 lg:col-span-6" aria-labelledby="recently-accessed-title">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-800">
                                Continue de onde parou
                            </p>

                            <h2 id="recently-accessed-title" class="mt-1 text-xl font-bold text-slate-900">
                                Últimos materiais acessados ou baixados
                            </h2>
                        </div>
                    </div>

                    <div class="w-full min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @forelse ($recentlyAccessedMaterials as $material)
                            <a href="{{ route('painel.library.show', ['material' => $material->getKey()]) }}"
                                class="group flex w-full min-w-0 items-center justify-between gap-4 border-b border-slate-100 p-4 transition last:border-b-0 hover:bg-blue-50/50 sm:p-5">
                                <span class="min-w-0 flex-1">
                                    <span
                                        class="block truncate text-base font-semibold text-slate-800 group-hover:text-blue-800">
                                        {{ $material->title }}
                                    </span>

                                    <span class="mt-1 block truncate text-sm text-slate-500">
                                        {{ $material->category->name }}
                                        <span aria-hidden="true">·</span>
                                        {{ $material->type }}
                                    </span>
                                </span>

                                <span class="shrink-0 whitespace-nowrap text-sm font-semibold text-blue-800">
                                    Abrir material
                                    <span aria-hidden="true">→</span>
                                </span>
                            </a>
                        @empty
                            <p class="px-6 py-10 text-center text-sm text-slate-500">
                                Você ainda não acessou materiais publicados.
                            </p>
                        @endforelse
                    </div>
                </section>


                {{-- Materiais publicados recentemente --}}
                <section class="w-full min-w-0 space-y-4 lg:col-span-6" aria-labelledby="recent-materials-title">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-800">
                                Descubra conteúdos
                            </p>

                            <h2 id="recent-materials-title" class="mt-1 text-xl font-bold text-slate-900">
                                Materiais publicados recentemente
                            </h2>
                        </div>

                        <a href="{{ route('painel.library') }}"
                            class="shrink-0 text-sm font-semibold text-blue-800 hover:text-blue-800">
                            Ver biblioteca
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>

                    <div class="w-full min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @forelse ($publishedMaterials as $material)
                            <a href="{{ route('painel.library.show', ['material' => $material->getKey()]) }}"
                                class="group flex items-center gap-4 border-b border-slate-100 p-4 transition last:border-b-0 hover:bg-blue-50/50 sm:p-5">
                                <span
                                    class="flex size-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-100 to-sky-50 text-blue-800 ring-1 ring-blue-100">
                                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.6" aria-hidden="true">
                                        <rect x="3" y="7" width="18" height="13" rx="2" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M8 13h8m-4-3v6" />
                                    </svg>
                                </span>

                                <span class="min-w-0 flex-1">
                                    <span
                                        class="block truncate text-base font-semibold text-slate-800 group-hover:text-blue-800">
                                        {{ $material->title }}
                                    </span>

                                    <span class="mt-1 block truncate text-sm text-slate-500">
                                        {{ $material->category->name }}
                                        <span aria-hidden="true">·</span>
                                        {{ $material->type }}
                                    </span>

                                    <span class="mt-1 block text-sm text-slate-400">
                                        {{ $material->published_at?->format('d/m/Y') ?? 'Data não informada' }}
                                    </span>
                                </span>

                                <svg class="size-5 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-blue-800"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                                </svg>
                            </a>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <span
                                    class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 3.75h8.5L19 8.25v12H6a2 2 0 0 1-2-2v-12a2 2 0 0 1 2-2Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 4v5h5" />
                                    </svg>
                                </span>

                                <p class="mt-3 text-base font-semibold text-slate-700">
                                    Ainda não há materiais publicados.
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    Quando houver novos conteúdos, eles aparecerão aqui.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>

            </div>
        @else
            {{-- ======================================== --}}
            {{-- DASHBOARD ADMIN / GESTÃO                 --}}
            {{-- ORIGINAL - NÃO ALTERADO                 --}}
            {{-- ======================================== --}}

            <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0 space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">
                        Visão Geral
                    </h1>

                    <p class="text-base text-slate-500 sm:text-lg">
                        Acompanhe os principais indicadores da sua biblioteca digital.
                    </p>
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

        <div class="grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-2">

            @if ($isTeacher)
            @else
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
            @endif
        </div>

        @unless ($isTeacher)
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
        @endunless
    </div>
</x-app-layout>
