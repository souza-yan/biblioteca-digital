<header class="rounded-2xl bg-blue-800 px-5 py-4 sm:px-7 sm:py-6">
    <x-skeleton.text class="w-fit text-xs font-semibold uppercase tracking-[0.14em] sm:text-sm">Gestão escolar</x-skeleton.text>
    <x-skeleton.text class="mt-1 w-fit text-2xl font-bold tracking-tight sm:mt-2 sm:text-4xl">{{ $staffSchool?->name }}</x-skeleton.text>
</header>
<section class="grid grid-cols-3 gap-2 sm:gap-4">
    @foreach ([['Professores vinculados', $activeUsersCount, 'vínculos ativos na escola'], ['Materiais publicados', $publishedMaterialsCount, 'disponíveis para consulta'], ['Downloads da escola', $recentDownloadsCount, 'nos últimos 30 dias']] as [$label, $value, $description])
        <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-3 text-center sm:p-5 sm:text-left">
            <x-skeleton.text class="text-xs font-semibold leading-4 sm:text-sm sm:leading-5">{{ $label }}</x-skeleton.text>
            <x-skeleton.text class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">{{ number_format($value, 0, ',', '.') }}</x-skeleton.text>
            <x-skeleton.text class="mt-1 hidden text-sm sm:block">{{ $description }}</x-skeleton.text>
        </article>
    @endforeach
</section>
<section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
    <x-skeleton.text class="w-fit text-lg font-bold sm:text-xl">Acesso rápido</x-skeleton.text>
    <div class="mt-4 grid grid-cols-3 gap-2 sm:mt-5 sm:gap-3 lg:gap-5">
        @foreach (['Professores e vínculos', 'Biblioteca de materiais', 'Dados da conta'] as $label)
            <div class="flex min-h-28 flex-col items-center justify-center gap-2 rounded-xl border border-slate-200 p-3 text-center text-xs font-semibold sm:min-h-32 sm:text-sm lg:min-h-40 lg:gap-4 lg:p-6 lg:text-lg"><x-skeleton.block class="size-10 rounded-xl lg:size-14 lg:rounded-2xl" /><x-skeleton.text>{{ $label }}</x-skeleton.text></div>
        @endforeach
    </div>
</section>
<div class="min-w-0">
    <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
        <div class="flex items-start justify-between gap-4"><x-skeleton.text class="text-lg font-bold sm:text-xl">Materiais mais acessados</x-skeleton.text><x-skeleton.text class="shrink-0 text-sm font-semibold">Ver materiais</x-skeleton.text></div>
        <div class="mt-4 space-y-2 sm:mt-5 sm:space-y-3">
            @forelse ($topDownloadedMaterials as $material)
                <div class="flex min-h-16 items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3 sm:gap-4 sm:p-4">
                    <x-skeleton.block class="size-9 shrink-0 rounded-full sm:size-10" />
                    <div class="min-w-0 flex-1"><x-skeleton.text class="truncate font-semibold">{{ $material->title }}</x-skeleton.text><x-skeleton.text class="mt-1 truncate text-sm">{{ $material->category->name }}</x-skeleton.text></div>
                    <x-skeleton.block class="h-6 w-12 shrink-0 rounded-full sm:h-7" />
                </div>
            @empty
                <div class="rounded-xl bg-slate-50 px-4 py-7"><x-skeleton.text class="text-center text-sm">Ainda não há downloads registrados para esta escola.</x-skeleton.text></div>
            @endforelse
        </div>
    </section>
</div>
