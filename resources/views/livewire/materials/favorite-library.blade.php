<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Meus favoritos</h1>
            <p class="mt-2 text-base text-slate-500">Seus materiais publicados salvos para consultar depois.</p>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($materials as $material)
                <article wire:key="favorite-material-mobile-{{ $material->id }}"
                    class="flex min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                    @if ($material->currentVersion)
                        <x-materials.pdf-cover :material="$material" :version="$material->currentVersion"
                            :alt="'Prévia de ' . $material->title" fit="contain" class="w-full rounded-lg" />
                    @else
                        <div class="flex aspect-[2480/3508] w-full items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-400"
                            aria-label="Prévia indisponível">
                            <x-ts-icon icon="document" outline class="size-6" />
                        </div>
                    @endif

                    <div class="min-w-0 pt-3">
                        <h2 class="break-words text-sm font-semibold leading-5 text-slate-900">{{ $material->title }}</h2>
                        <p class="mt-1 break-words text-xs text-slate-600">{{ $material->category->name }}</p>
                    </div>

                    <div class="mt-3 flex items-center justify-end gap-1.5 border-t border-slate-100 pt-3">
                        <x-ts-button color="yellow" wire:click="removeFavorite({{ $material->id }})"
                            wire:loading.attr="disabled" wire:target="removeFavorite({{ $material->id }})"
                            aria-label="Remover {{ $material->title }} dos favoritos" title="Remover dos favoritos"
                            class="!size-9 !p-0 shrink-0 justify-center">
                            <x-ts-icon icon="bookmark" solid class="size-5 text-amber-950" />
                        </x-ts-button>
                        <x-icon-button icon="eye" wire:click="openMobileMaterialDetail({{ $material->id }})"
                            wire:loading.attr="disabled"
                            wire:target="openMobileMaterialDetail({{ $material->id }})"
                            label="Ver detalhes de {{ $material->title }}" class="!size-9 [&_svg]:!size-5" />
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-slate-200 bg-white px-4 py-10 text-center text-slate-600">
                    <span class="text-base">Você ainda não tem materiais favoritos publicados.</span>
                </div>
            @endforelse
        </div>

        <x-ts-modal wire="showMobileMaterialDetail" size="lg" center
            x-on:mobile-material-preview-closed.window="show = true">
            @include('livewire.materials.partials.mobile-material-detail', [
                'material' => $mobileMaterial,
                'previewUrl' => $mobilePreviewUrl,
            ])

            <x-slot:footer>
                <div class="flex w-full items-center justify-between">
                    @if ($mobileMaterial?->currentVersion && $mobilePreviewUrl)
                        <button type="button" x-on:click="$dispatch('mobile-material-preview')"
                            aria-label="Visualizar conteúdo" title="Visualizar conteúdo"
                            class="inline-flex size-11 items-center justify-center rounded-lg bg-blue-800 text-white shadow-sm transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                            <x-ts-icon icon="magnifying-glass" outline class="size-6" />
                        </button>
                    @else
                        <span></span>
                    @endif
                    @if ($mobileMaterial?->currentVersion)
                        <x-icon-button icon="document-arrow-down" variant="success"
                            :href="route('downloads.show', $mobileMaterial)"
                            label="Baixar {{ $mobileMaterial->title }}" />
                    @endif
                </div>
            </x-slot:footer>
        </x-ts-modal>

        <x-pagination :paginator="$materials" class="mt-4" />
    </div>
</div>
