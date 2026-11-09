<header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <x-skeleton.text class="w-fit text-3xl font-bold tracking-tight sm:text-4xl">Visão Geral</x-skeleton.text>
    <x-skeleton.text class="w-fit shrink-0 text-base font-semibold">Ver materiais ›</x-skeleton.text>
</header>
<section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([['Total de materiais', $totalMaterials, 'cadastros no sistema'], ['Total de categorias', $categoryCounts['total'], 'categorias cadastradas'], ['Usuários ativos', $activeUsersCount, 'com status ativo'], ['Downloads', $recentDownloadsCount, 'nos últimos 30 dias']] as [$label, $value, $description])
        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <x-skeleton.block class="size-14 shrink-0 rounded-2xl" />
            <div class="min-w-0"><x-skeleton.text class="text-lg font-semibold">{{ $label }}</x-skeleton.text><x-skeleton.text class="mt-1 text-4xl font-bold tracking-tight sm:text-5xl">{{ number_format($value, 0, ',', '.') }}</x-skeleton.text><x-skeleton.text class="mt-1 text-base">{{ $description }}</x-skeleton.text></div>
        </article>
    @endforeach
</section>
<div class="grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-2">
    <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 xl:p-8">
        <x-skeleton.text class="w-fit text-2xl font-bold">Materiais por status</x-skeleton.text><x-skeleton.text class="mt-1 w-fit text-base">Status cadastral do acervo da biblioteca.</x-skeleton.text>
        <div class="mt-6 space-y-5">
            @foreach ($statusCounts as $status => $total)
                <div><div class="flex items-baseline justify-between gap-3"><x-skeleton.text class="text-base font-semibold">{{ $status }}</x-skeleton.text><x-skeleton.text class="shrink-0 text-base font-semibold">{{ number_format($total, 0, ',', '.') }} / {{ number_format($totalMaterials, 0, ',', '.') }}</x-skeleton.text></div><x-skeleton.block class="mt-2 h-2.5 rounded-full" /></div>
            @endforeach
        </div>
    </section>
    <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 xl:p-8">
        <x-skeleton.text class="w-fit text-2xl font-bold">Categorias</x-skeleton.text><x-skeleton.text class="mt-1 w-fit text-base">Organização dos temas cadastrados no acervo.</x-skeleton.text>
        <div class="mt-6 space-y-5">
            @foreach (['Total', 'Ativas', 'Inativas'] as $label)
                <div><div class="flex items-baseline justify-between gap-3"><x-skeleton.text class="text-base font-semibold">{{ $label }}</x-skeleton.text><x-skeleton.line class="w-16" /></div><x-skeleton.block class="mt-2 h-2.5 rounded-full" /></div>
            @endforeach
        </div>
        <x-skeleton.text class="mt-6 w-fit text-base font-semibold">Gerenciar categorias ›</x-skeleton.text>
    </article>
</div>
<div class="grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-2">
    <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 xl:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"><div class="min-w-0"><x-skeleton.text class="text-2xl font-bold">Materiais mais baixados</x-skeleton.text><x-skeleton.text class="mt-1 text-base">Distribuição dos downloads no período selecionado.</x-skeleton.text></div><x-skeleton.text class="w-fit shrink-0 text-base font-semibold">Ver todas ›</x-skeleton.text></div>
        <div class="mt-6 space-y-5">
            @forelse ($topDownloadedMaterials as $material)
                <div class="min-w-0"><div class="flex items-baseline justify-between gap-3"><x-skeleton.text class="min-w-0 truncate text-base font-semibold">{{ $material->title }}</x-skeleton.text><x-skeleton.text class="shrink-0 text-base font-semibold">{{ number_format($material->downloads_count, 0, ',', '.') }}</x-skeleton.text></div><x-skeleton.text class="mt-0.5 truncate text-base">{{ $material->category->name }}</x-skeleton.text><x-skeleton.block class="mt-2 h-2.5 rounded-full" /></div>
            @empty
                <div class="py-8"><x-skeleton.text class="text-center text-base">Nenhum material baixado neste período.</x-skeleton.text></div>
            @endforelse
        </div>
    </section>
    <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 xl:p-8">
        <div class="flex items-start justify-between gap-4"><div class="min-w-0"><x-skeleton.text class="text-2xl font-bold">Atividades recentes</x-skeleton.text><x-skeleton.text class="mt-1 text-base">Últimos registros no sistema.</x-skeleton.text></div><x-skeleton.text class="w-fit shrink-0 text-base font-semibold">Ver todas ›</x-skeleton.text></div>
        <div class="mt-6 divide-y divide-slate-100">
            @forelse ($recentActivities as $activity)
                <div class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0"><div class="min-w-0"><x-skeleton.text class="text-base font-semibold leading-6">{{ $activity->description }}</x-skeleton.text><x-skeleton.text class="mt-1 truncate text-base">{{ $activity->user->name }} @if ($activity->material) · {{ $activity->material->title }} @endif</x-skeleton.text></div><x-skeleton.text class="shrink-0 text-base">{{ $activity->created_at->format('d/m H:i') }}</x-skeleton.text></div>
            @empty
                <div class="py-8"><x-skeleton.text class="text-center text-base">Nenhuma atividade registrada.</x-skeleton.text></div>
            @endforelse
        </div>
    </article>
</div>
