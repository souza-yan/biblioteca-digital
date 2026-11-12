@can('update', $category)
<div class="flex justify-end gap-2">
    <x-ts-button color="blue"
        wire:click="editCategory({{ $category->id }})" aria-label="Editar categoria {{ $category->name }}"
        title="Editar categoria" class="!size-12 !p-0 justify-center">
        <x-ts-icon icon="pencil-square" outline class="size-7 text-white" />
    </x-ts-button>
    <x-ts-button :color="$category->is_active ? 'red' : 'emerald'"
        x-on:click="$tsui.interaction('dialog').wireable($wire.$id).question('Alterar situação da categoria?', 'Confirme a ativação ou desativação desta categoria.').confirm('Confirmar', 'toggleActive', {{ $category->id }}).cancel('Cancelar').send()"
        wire:loading.attr="disabled" wire:target="toggleActive({{ $category->id }})"
        :aria-label="$category->is_active ? 'Desativar categoria '.$category->name : 'Ativar categoria '.$category->name"
        :title="$category->is_active ? 'Desativar categoria' : 'Ativar categoria'" class="!size-12 !p-0 justify-center">
        <x-ts-icon :icon="$category->is_active ? 'no-symbol' : 'check-circle'" outline class="size-7 text-white" />
    </x-ts-button>
</div>
@endcan
