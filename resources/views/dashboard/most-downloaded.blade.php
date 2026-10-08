<x-app-layout>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-700">Painel</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-blue-900 sm:text-4xl">
                    Materiais mais baixados
                </h1>
                <p class="mt-2 text-base text-slate-500">
                    Ranking de materiais pelo número de downloads no período selecionado.
                </p>
            </div>

            <a href="{{ route('dashboard', ['downloadPeriod' => $downloadPeriod]) }}"
                class="shrink-0 text-base font-semibold text-blue-700 hover:text-blue-900">
                Voltar ao painel
                <span aria-hidden="true">›</span>
            </a>
        </header>

        <section class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
            aria-label="Ranking de materiais">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <form method="GET" action="{{ route('painel.materials.most-downloaded') }}"
                    class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <label for="downloadPeriod" class="text-sm font-medium text-slate-700">
                        Período dos downloads
                        <select id="downloadPeriod" name="downloadPeriod"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:min-w-52">
                            @foreach ($downloadPeriodOptions as $value => $label)
                                <option value="{{ $value }}" @selected($downloadPeriod === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit"
                        class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-900">
                        Aplicar
                    </button>
                </form>

                <p class="text-sm text-slate-500">
                    {{ number_format($topDownloadedMaterials->total(), 0, ',', '.') }}
                    {{ $topDownloadedMaterials->total() === 1 ? 'material' : 'materiais' }}
                </p>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($topDownloadedMaterials as $material)
                    <a href="{{ route('painel.materials.show', ['material' => $material->getKey()]) }}"
                        class="flex items-center gap-4 py-4 first:pt-0 last:pb-0">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-blue-800">
                            {{ $loop->iteration + (($topDownloadedMaterials->currentPage() - 1) * $topDownloadedMaterials->perPage()) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-base font-semibold text-slate-800">
                                {{ $material->title }}
                            </span>
                            <span class="mt-1 block truncate text-sm text-slate-500">
                                {{ $material->category?->name ?? 'Sem categoria' }}
                            </span>
                        </span>
                        <span class="shrink-0 text-right">
                            <span class="block text-base font-semibold text-slate-800">
                                {{ number_format($material->downloads_count, 0, ',', '.') }}
                            </span>
                            <span class="text-sm text-slate-500">
                                {{ $material->downloads_count === 1 ? 'download' : 'downloads' }}
                            </span>
                        </span>
                    </a>
                @empty
                    <p class="py-10 text-center text-base text-slate-500">
                        Nenhum material baixado neste período.
                    </p>
                @endforelse
            </div>

            @if ($topDownloadedMaterials->hasPages())
                <div class="border-t border-slate-100 pt-4">
                    {{ $topDownloadedMaterials->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
