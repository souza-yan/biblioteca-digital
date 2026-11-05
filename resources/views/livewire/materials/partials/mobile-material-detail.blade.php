@if ($material)
    <div x-data="{ isPreviewOpen: false }" x-on:mobile-material-preview.window="isPreviewOpen = true">
        <h2 class="text-lg font-semibold text-slate-900">{{ $material->title }}</h2>
        <p class="mt-1 text-sm text-slate-600">Por {{ $material->author }}</p>

        <dl class="mt-3 grid grid-cols-1 gap-2 border-t border-slate-100 pt-3 text-sm">
            <div>
                <dt class="font-semibold text-slate-500">Categoria</dt>
                <dd class="mt-1 text-slate-900">{{ $material->category->name }}</dd>
            </div>
            @if ($material->description)
                <div>
                    <dt class="font-semibold text-slate-500">Descrição</dt>
                    <dd class="mt-1 whitespace-pre-line text-slate-700">{{ $material->description }}</dd>
                </div>
            @endif
        </dl>

        @if ($material->currentVersion && $previewUrl)
            @include('livewire.materials.partials.file-preview', [
                'version' => $material->currentVersion,
                'previewUrl' => $previewUrl,
                'showPreviewButtonOnMobile' => true,
                'compact' => true,
                'iconOnlyPreviewButton' => true,
                'showPreviewAction' => false,
            ])
        @endif
    </div>
@endif
