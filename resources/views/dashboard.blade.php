<x-app-layout :uses-element-skeletons="$isTeacher">
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
                <div class="max-w-2xl">
                    <h1 id="teacher-welcome-title" class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Encontre recursos para suas aulas
                    </h1>
                    <p class="mt-3 text-base text-blue-100 sm:text-lg">
                        Explore projetos, atividades e materiais de robótica educacional.
                    </p>
                </div>

                <livewire:materials.home-material-search lazy="on-load" />
            </section>

            <livewire:dashboard.teacher-dashboard-content lazy="on-load" />
        @elseif ($isStaff)
            <header class="rounded-2xl bg-blue-800 px-5 py-4 text-white sm:px-7 sm:py-6">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-200 sm:text-sm">Gestão escolar</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight sm:mt-2 sm:text-4xl">{{ $staffSchool?->name }}</h1>
            </header>

            <section aria-label="Resumo da escola" class="grid grid-cols-3 gap-2 sm:gap-4">
                <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-3 text-center sm:p-5 sm:text-left">
                    <p class="text-xs font-semibold leading-4 text-slate-500 sm:text-sm sm:leading-5">Professores vinculados</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($activeUsersCount, 0, ',', '.') }}</p>
                    <p class="mt-1 hidden text-sm text-slate-500 sm:block">vínculos ativos na escola</p>
                </article>
                <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-3 text-center sm:p-5 sm:text-left">
                    <p class="text-xs font-semibold leading-4 text-slate-500 sm:text-sm sm:leading-5">Materiais publicados</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($publishedMaterialsCount, 0, ',', '.') }}</p>
                    <p class="mt-1 hidden text-sm text-slate-500 sm:block">disponíveis para consulta</p>
                </article>
                <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-3 text-center sm:p-5 sm:text-left">
                    <p class="text-xs font-semibold leading-4 text-slate-500 sm:text-sm sm:leading-5">Downloads da escola</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($recentDownloadsCount, 0, ',', '.') }}</p>
                    <p class="mt-1 hidden text-sm text-slate-500 sm:block">nos últimos 30 dias</p>
                </article>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6" aria-labelledby="staff-actions-title">
                <h2 id="staff-actions-title" class="text-lg font-bold text-slate-900 sm:text-xl">Acesso rápido</h2>
                <div class="mt-4 grid grid-cols-3 gap-2 sm:mt-5 sm:gap-3">
                    <a href="{{ route('painel.users') }}"
                        class="flex min-h-28 flex-col items-center justify-center gap-2 rounded-xl border border-slate-200 p-3 text-center text-xs font-semibold text-slate-800 transition hover:border-blue-200 hover:bg-blue-50 sm:min-h-32 sm:text-sm">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-800">
                            <x-ts-icon icon="users" outline class="size-5" />
                        </span>
                        <span>Professores e vínculos</span>
                    </a>
                    <a href="{{ route('painel.materials') }}"
                        class="flex min-h-28 flex-col items-center justify-center gap-2 rounded-xl border border-slate-200 p-3 text-center text-xs font-semibold text-slate-800 transition hover:border-blue-200 hover:bg-blue-50 sm:min-h-32 sm:text-sm">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                            <x-ts-icon icon="book-open" outline class="size-5" />
                        </span>
                        <span>Biblioteca de materiais</span>
                    </a>
                    <a href="{{ route('profile.show') }}"
                        class="flex min-h-28 flex-col items-center justify-center gap-2 rounded-xl border border-slate-200 p-3 text-center text-xs font-semibold text-slate-800 transition hover:border-blue-200 hover:bg-blue-50 sm:min-h-32 sm:text-sm">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-violet-50 text-violet-700">
                            <x-ts-icon icon="user-circle" outline class="size-5" />
                        </span>
                        <span>Dados da conta</span>
                    </a>
                </div>
            </section>

            <div>
                <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6" aria-labelledby="staff-materials-title">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 id="staff-materials-title" class="text-lg font-bold text-slate-900 sm:text-xl">Materiais mais acessados</h2>
                        </div>
                        <a href="{{ route('painel.materials') }}" class="shrink-0 text-sm font-semibold text-blue-800 hover:underline">Ver materiais</a>
                    </div>
                    <div class="mt-4 space-y-2 sm:mt-5 sm:space-y-3">
                        @forelse ($topDownloadedMaterials as $material)
                            <a href="{{ route('painel.materials.show', $material) }}"
                                class="flex min-h-16 items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3 transition hover:border-blue-200 hover:bg-blue-50 sm:gap-4 sm:p-4">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-blue-800 text-sm font-bold text-white sm:size-10">
                                    {{ $loop->iteration }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-semibold text-slate-800">{{ $material->title }}</span>
                                    <span class="mt-1 block truncate text-sm text-slate-500">{{ $material->category->name }}</span>
                                </span>
                                <span class="flex shrink-0 items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-800 sm:px-3 sm:text-sm">
                                    <x-ts-icon icon="arrow-down-tray" outline class="size-3.5" />
                                    {{ number_format($material->downloads_count, 0, ',', '.') }}
                                </span>
                            </a>
                        @empty
                            <div class="rounded-xl bg-slate-50 px-4 py-7 text-center text-sm text-slate-500">Ainda não há downloads registrados para esta escola.</div>
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
