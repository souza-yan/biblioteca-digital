<x-app-layout>
    @php
        $currentUser = auth()->user();
        $libraryRoute = $isTeacher ? 'painel.library' : 'painel.materials';
        $totalMaterials = $isTeacher ? null : $statusCounts->sum();
        $publishedMaterialsCount = $isTeacher ? null : $statusCounts->get('Publicado', 0);
    @endphp

    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <section
            class="relative isolate overflow-hidden rounded-2xl bg-blue-950 px-6 py-8 text-white shadow-sm sm:px-9 sm:py-10 lg:px-12 lg:py-12"
            aria-labelledby="dashboard-title">
            <div class="absolute inset-y-0 right-0 -z-10 hidden w-[48%] overflow-hidden lg:block" aria-hidden="true">
                <div class="absolute inset-0 bg-gradient-to-l from-blue-700/30 via-blue-800/15 to-transparent"></div>
                <div class="absolute -right-12 top-1/2 size-80 -translate-y-1/2 rounded-full border border-white/10">
                </div>
                <div class="absolute right-8 top-1/2 size-56 -translate-y-1/2 rounded-full border border-white/10"></div>
                <div class="absolute right-24 top-1/2 size-32 -translate-y-1/2 rounded-full border border-white/10">
                </div>
                <div
                    class="absolute right-24 top-1/2 flex size-32 -translate-y-1/2 items-center justify-center rounded-3xl border border-white/15 bg-white/5 shadow-inner">
                    <svg class="size-20 text-white/90" viewBox="0 0 96 96" fill="none" stroke="currentColor"
                        stroke-width="2.2" aria-hidden="true">
                        <rect x="24" y="28" width="48" height="42" rx="12" />
                        <path stroke-linecap="round" d="M48 18v10M18 44h6M72 44h6M34 70v8m28-8v8M38 44h.1M58 44h.1" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M38 55c3 3 6.3 4.5 10 4.5s7-1.5 10-4.5" />
                        <circle cx="48" cy="15" r="3" fill="currentColor" stroke="none" />
                    </svg>
                </div>
            </div>

            <div class="absolute left-0 top-0 h-1.5 w-full bg-gradient-to-r from-amber-400 via-sky-400 to-blue-500"
                aria-hidden="true"></div>

            <div class="relative max-w-2xl">
                <p class="mb-4 text-sm font-bold uppercase tracking-[0.18em] text-blue-200">
                    Secretaria Municipal de Educação
                </p>
                <h1 id="dashboard-title"
                    class="max-w-xl text-3xl font-bold leading-tight tracking-tight sm:text-[2rem]">
                    Biblioteca Digital<br class="hidden sm:block"> de Robótica
                </h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-blue-100 sm:text-lg">
                    Olá, {{ $currentUser->name }}. Encontre materiais, conteúdos e recursos para apoiar o ensino de
                    robótica nas escolas.
                </p>
                <div class="mt-6">
                    <x-ts-button color="blue" href="{{ route($libraryRoute) }}" class="px-5 py-3 text-base">
                        {{ $isTeacher ? 'Explorar materiais' : 'Gerenciar materiais' }}
                    </x-ts-button>
                </div>
            </div>
        </section>

        @if ($isTeacher)
            <section aria-label="Biblioteca em números" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <a href="{{ route('painel.library.categories') }}"
                    class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                    <span
                        class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" />
                            <path stroke-linecap="round" d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20M8 7h8M8 10h5" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-base font-semibold text-slate-800">Materiais publicados</span>
                        <span class="mt-1 block text-sm leading-6 text-slate-500">Pesquise conteúdos disponíveis para
                            consulta.</span>
                    </span>
                    <svg class="size-5 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-blue-700"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                    </svg>
                </a>
                <a href="{{ route('painel.favorites') }}"
                    class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                    <span
                        class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.8 8.8c0 4.2-8.8 10.2-8.8 10.2S3.2 13 3.2 8.8A4.3 4.3 0 0 1 12 6.7a4.3 4.3 0 0 1 8.8 2.1Z" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-base font-semibold text-slate-800">Meus favoritos</span>
                        <span class="mt-1 block text-sm leading-6 text-slate-500">Acesse rapidamente os materiais que
                            salvou.</span>
                    </span>
                    <svg class="size-5 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-blue-700"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                    </svg>
                </a>
            </section>
        @else
            <section aria-label="Resumo administrativo" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Total de materiais</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-blue-950">
                                {{ number_format($totalMaterials, 0, ',', '.') }}</p>
                        </div>
                        <span class="flex size-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 3.75h8.5L19 8.25v12H6a2 2 0 0 1-2-2v-12a2 2 0 0 1 2-2Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 4v5h5M8 13h8m-8 4h8" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Em todos os status cadastrados</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Materiais publicados</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-blue-950">
                                {{ number_format($publishedMaterialsCount, 0, ',', '.') }}</p>
                        </div>
                        <span
                            class="flex size-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                <circle cx="12" cy="12" r="9" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Disponíveis na biblioteca</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Usuários ativos</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-blue-950">
                                {{ number_format($activeUsersCount, 0, ',', '.') }}</p>
                        </div>
                        <span
                            class="flex size-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-700">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <circle cx="9" cy="8" r="3.5" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.5 20v-1.3a4.7 4.7 0 0 1 4.7-4.7h3.6a4.7 4.7 0 0 1 4.7 4.7V20h-13Z" />
                                <path stroke-linecap="round"
                                    d="M16 5a3.5 3.5 0 0 1 0 6.8m2 2.5a4.7 4.7 0 0 1 3.5 4.5V20" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Contas habilitadas no sistema</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Downloads</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-blue-950">
                                {{ number_format($recentDownloadsCount, 0, ',', '.') }}</p>
                        </div>
                        <span class="flex size-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v3h16v-3" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Nos últimos 30 dias</p>
                </article>
            </section>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            @if ($isTeacher)
                <section class="space-y-4 xl:col-span-7" aria-labelledby="recent-materials-title">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-700">Descubra
                                conteúdos</p>
                            <h2 id="recent-materials-title" class="mt-1 text-xl font-bold text-slate-900">Materiais
                                publicados recentemente</h2>
                        </div>
                        <a href="{{ route('painel.library') }}"
                            class="shrink-0 text-sm font-semibold text-blue-700 hover:text-blue-900">Ver biblioteca
                            <span aria-hidden="true">→</span></a>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @forelse ($publishedMaterials as $material)
                            <a href="{{ route('painel.library.show', $material) }}"
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
                                        class="block truncate text-base font-semibold text-slate-800 group-hover:text-blue-900">{{ $material->title }}</span>
                                    <span
                                        class="mt-1 block truncate text-sm text-slate-500">{{ $material->category->name }}
                                        <span aria-hidden="true">·</span> {{ $material->type }}</span>
                                    <span class="mt-1 block text-sm text-slate-400">
                                        {{ $material->published_at?->format('d/m/Y') ?? 'Data não informada' }}
                                    </span>
                                </span>
                                <svg class="size-5 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-blue-700"
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
                                <p class="mt-3 text-base font-semibold text-slate-700">Ainda não há materiais publicados.
                                </p>
                                <p class="mt-1 text-sm text-slate-500">Quando houver novos conteúdos, eles aparecerão
                                    aqui.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="space-y-4 xl:col-span-5" aria-labelledby="favorites-title">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-rose-600">Salvos por você
                            </p>
                            <h2 id="favorites-title" class="mt-1 text-xl font-bold text-slate-900">Meus favoritos</h2>
                        </div>
                        <a href="{{ route('painel.favorites') }}"
                            class="shrink-0 text-sm font-semibold text-blue-700 hover:text-blue-900">Ver todos <span
                                aria-hidden="true">→</span></a>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        @forelse ($favoriteMaterials as $material)
                            <a href="{{ route('painel.library.show', $material) }}"
                                class="group flex items-center gap-3 border-b border-slate-100 py-3 first:pt-0 last:border-b-0 last:pb-0">
                                <span
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M20.8 8.8c0 4.2-8.8 10.2-8.8 10.2S3.2 13 3.2 8.8A4.3 4.3 0 0 1 12 6.7a4.3 4.3 0 0 1 8.8 2.1Z" />
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span
                                        class="block truncate text-base font-medium text-slate-800 group-hover:text-blue-900">{{ $material->title }}</span>
                                    <span
                                        class="mt-0.5 block truncate text-sm text-slate-500">{{ $material->category->name }}
                                        <span aria-hidden="true">·</span> {{ $material->type }}</span>
                                </span>
                                <svg class="size-4 shrink-0 text-slate-300 group-hover:text-blue-700"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                                </svg>
                            </a>
                        @empty
                            <div class="py-10 text-center">
                                <p class="text-base font-semibold text-slate-700">Você ainda não favoritou materiais.</p>
                                <p class="mt-1 text-sm text-slate-500">Salve conteúdos na biblioteca para encontrá-los
                                    aqui.</p>
                                <a href="{{ route('painel.library') }}"
                                    class="mt-4 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-900">Explorar
                                    materiais <span class="ml-1" aria-hidden="true">→</span></a>
                            </div>
                        @endforelse
                    </div>
                </section>
            @else
                <section class="space-y-4 xl:col-span-7" aria-labelledby="activities-title">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-700">Acompanhamento
                            </p>
                            <h2 id="activities-title" class="mt-1 text-xl font-bold text-slate-900">Atividades
                                recentes</h2>
                        </div>
                        <a href="{{ route('painel.activities') }}"
                            class="shrink-0 text-sm font-semibold text-blue-700 hover:text-blue-900">Ver atividades
                            <span aria-hidden="true">→</span></a>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @forelse ($recentActivities as $activity)
                            <article
                                class="flex items-start gap-3 border-b border-slate-100 p-4 last:border-b-0 sm:gap-4 sm:p-5">
                                <span
                                    class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-base font-medium leading-6 text-slate-800">
                                        {{ $activity->description }}</p>
                                    <p class="mt-1 truncate text-sm text-slate-500">
                                        {{ $activity->user->name }}
                                        @if ($activity->material)
                                            <span aria-hidden="true">·</span> {{ $activity->material->title }}
                                        @endif
                                    </p>
                                </div>
                                <time class="shrink-0 pt-0.5 text-sm text-slate-400"
                                    datetime="{{ $activity->created_at->toIso8601String() }}">
                                    {{ $activity->created_at->format('d/m H:i') }}
                                </time>
                            </article>
                        @empty
                            <div class="px-6 py-12 text-center text-base text-slate-500">Nenhuma atividade registrada.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="space-y-4 xl:col-span-5" aria-labelledby="materials-status-title">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-700">Acervo</p>
                        <h2 id="materials-status-title" class="mt-1 text-xl font-bold text-slate-900">Materiais por status</h2>
                    </div>
                    <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        @foreach ($statusCounts as $status => $total)
                            @php
                                $statusStyle = match ($status) {
                                    'Publicado' => [
                                        'bg' => 'bg-emerald-50',
                                        'text' => 'text-emerald-700',
                                        'bar' => 'bg-emerald-500',
                                    ],
                                    'Arquivado' => [
                                        'bg' => 'bg-slate-100',
                                        'text' => 'text-slate-600',
                                        'bar' => 'bg-slate-400',
                                    ],
                                    default => [
                                        'bg' => 'bg-amber-50',
                                        'text' => 'text-amber-700',
                                        'bar' => 'bg-amber-400',
                                    ],
                                };
                                $percentage =
                                    $totalMaterials > 0 ? min(100, (int) round(($total / $totalMaterials) * 100)) : 0;
                            @endphp
                            <div class="rounded-xl border border-slate-100 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="inline-flex items-center gap-2 text-base font-semibold text-slate-700">
                                        <span class="size-2 rounded-full {{ $statusStyle['bar'] }}"
                                            aria-hidden="true"></span>
                                        {{ $status }}
                                    </span>
                                    <span
                                        class="rounded-lg px-2.5 py-1 text-base font-bold {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }}">{{ number_format($total, 0, ',', '.') }}</span>
                                </div>
                                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100" role="progressbar"
                                    aria-label="Proporção de materiais {{ mb_strtolower($status) }}"
                                    aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="h-full rounded-full {{ $statusStyle['bar'] }}"
                                        style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <section aria-labelledby="quick-links-title">
            <div class="mb-4">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-700">Acesso rápido</p>
                <h2 id="quick-links-title" class="mt-1 text-xl font-bold text-slate-900">O que você precisa fazer?
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @if ($isTeacher)
                    <a href="{{ route('painel.library') }}"
                        class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <circle cx="10.8" cy="10.8" r="6.8" />
                                <path stroke-linecap="round" d="m16 16 4.5 4.5" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-base font-semibold text-slate-800">Pesquisar materiais</span>
                            <span class="mt-1 block truncate text-sm text-slate-500">Encontre por título ou
                                autor</span>
                        </span>
                        <span class="text-lg text-blue-600 transition group-hover:translate-x-0.5"
                            aria-hidden="true">→</span>
                    </a>
                    <a href="{{ route('painel.library') }}"
                        class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-700">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 7.5A1.5 1.5 0 0 1 4.5 6H10l2 2h7.5A1.5 1.5 0 0 1 21 9.5v8a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5v-10Z" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-base font-semibold text-slate-800">Explorar categorias</span>
                            <span class="mt-1 block truncate text-sm text-slate-500">Filtre a biblioteca por
                                categoria</span>
                        </span>
                        <span class="text-lg text-blue-600 transition group-hover:translate-x-0.5"
                            aria-hidden="true">→</span>
                    </a>
                    <a href="{{ route('painel.favorites') }}"
                        class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.8 8.8c0 4.2-8.8 10.2-8.8 10.2S3.2 13 3.2 8.8A4.3 4.3 0 0 1 12 6.7a4.3 4.3 0 0 1 8.8 2.1Z" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-base font-semibold text-slate-800">Meus favoritos</span>
                            <span class="mt-1 block truncate text-sm text-slate-500">Acesse seus materiais
                                salvos</span>
                        </span>
                        <span class="text-lg text-blue-600 transition group-hover:translate-x-0.5"
                            aria-hidden="true">→</span>
                    </a>
                    <a href="{{ route('profile.show') }}"
                        class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <circle cx="12" cy="8" r="3.5" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5 21v-1.5a7 7 0 0 1 14 0V21H5Z" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-base font-semibold text-slate-800">Meu perfil</span>
                            <span class="mt-1 block truncate text-sm text-slate-500">Consulte seus dados da
                                conta</span>
                        </span>
                        <span class="text-lg text-blue-600 transition group-hover:translate-x-0.5"
                            aria-hidden="true">→</span>
                    </a>
                @else
                    <a href="{{ route('painel.materials') }}"
                        class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 3.75h8.5L19 8.25v12H6a2 2 0 0 1-2-2v-12a2 2 0 0 1 2-2Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 4v5h5M8 13h8m-8 4h8" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-base font-semibold text-slate-800">Materiais</span>
                            <span class="mt-1 block truncate text-sm text-slate-500">Organize o acervo da
                                biblioteca</span>
                        </span>
                        <span class="text-lg text-blue-600 transition group-hover:translate-x-0.5"
                            aria-hidden="true">→</span>
                    </a>
                    <a href="{{ route('painel.categories') }}"
                        class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 7.5A1.5 1.5 0 0 1 4.5 6H10l2 2h7.5A1.5 1.5 0 0 1 21 9.5v8a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5v-10Z" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-base font-semibold text-slate-800">Categorias</span>
                            <span class="mt-1 block truncate text-sm text-slate-500">Mantenha os temas
                                organizados</span>
                        </span>
                        <span class="text-lg text-blue-600 transition group-hover:translate-x-0.5"
                            aria-hidden="true">→</span>
                    </a>
                    <a href="{{ route('painel.users') }}"
                        class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-700">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <circle cx="9" cy="8" r="3.5" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.5 20v-1.3a4.7 4.7 0 0 1 4.7-4.7h3.6a4.7 4.7 0 0 1 4.7 4.7V20h-13Z" />
                                <path stroke-linecap="round"
                                    d="M16 5a3.5 3.5 0 0 1 0 6.8m2 2.5a4.7 4.7 0 0 1 3.5 4.5V20" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-base font-semibold text-slate-800">Usuários</span>
                            <span class="mt-1 block truncate text-sm text-slate-500">Gerencie as contas de
                                acesso</span>
                        </span>
                        <span class="text-lg text-blue-600 transition group-hover:translate-x-0.5"
                            aria-hidden="true">→</span>
                    </a>
                    <a href="{{ route('painel.activities') }}"
                        class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 12h4l2.2-6 4.1 12 2.2-6H21" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-base font-semibold text-slate-800">Atividades</span>
                            <span class="mt-1 block truncate text-sm text-slate-500">Acompanhe os registros
                                recentes</span>
                        </span>
                        <span class="text-lg text-blue-600 transition group-hover:translate-x-0.5"
                            aria-hidden="true">→</span>
                    </a>
                @endif
            </div>
        </section>

        <footer class="overflow-hidden rounded-2xl bg-blue-950 text-white">
            <div class="h-1 bg-gradient-to-r from-amber-400 via-sky-400 to-blue-500" aria-hidden="true"></div>
            <div class="flex flex-col gap-2 px-5 py-4 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p class="font-semibold">Prefeitura Municipal de Caraguatatuba <span class="mx-1 text-blue-300"
                        aria-hidden="true">|</span> Secretaria Municipal de Educação</p>
                <p class="text-blue-200">Biblioteca Digital de Robótica</p>
            </div>
        </footer>
    </div>
</x-app-layout>
