<div class="flex flex-wrap md:flex-nowrap items-center justify-end gap-2">
@can('update', $material)
    @if ($actor?->isStaff())
        <x-ts-button color="blue" wire:click="editMaterial({{ $material->id }})"
            aria-label="Editar material {{ $material->title }}" title="Editar material"
            class="!size-12 !p-0 shrink-0 justify-center">
            <x-ts-icon icon="pencil-square" outline class="size-7 text-white" />
        </x-ts-button>
    @else
        <x-ts-button color="blue" href="{{ route('painel.materials.show', $material) }}"
            aria-label="Editar material {{ $material->title }}" title="Editar material"
            class="!size-12 !p-0 shrink-0 justify-center">
            <x-ts-icon icon="pencil-square" outline class="size-7 text-white" />
        </x-ts-button>
    @endif

@if ($material->status->value !== \App\Enums\MaterialStatus::PUBLISHED->value)
    <x-ts-button color="emerald" wire:click="publish({{ $material->id }})"
        wire:loading.attr="disabled" wire:target="publish({{ $material->id }})"
        aria-label="Publicar material {{ $material->title }}" title="Publicar material"
        class="!size-12 !p-0 shrink-0 justify-center">
        <x-ts-icon icon="cloud-arrow-up" outline class="size-7 text-white" />
    </x-ts-button>
@endif

@if ($material->status->value !== \App\Enums\MaterialStatus::ARCHIVED->value)
    <x-ts-button color="yellow" wire:click="archive({{ $material->id }})"
        wire:loading.attr="disabled" wire:target="archive({{ $material->id }})"
        aria-label="Arquivar material {{ $material->title }}" title="Arquivar material"
        class="!size-12 !p-0 shrink-0 justify-center">
        <x-ts-icon icon="archive-box" outline class="size-7 text-white" />
    </x-ts-button>
@endif
@endcan
</div>
