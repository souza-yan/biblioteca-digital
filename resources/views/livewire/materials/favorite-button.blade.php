<div>
    <x-ts-button :color="$isFavorited ? 'yellow' : 'blue'" wire:click="toggle"
        :aria-label="$isFavorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
        :title="$isFavorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'" wire:loading.attr="disabled"
        wire:target="toggle" class="!size-10 !p-0 justify-center">
        @if ($isFavorited)
            <x-ts-icon icon="bookmark" solid class="size-5 text-amber-950" />
        @else
            <x-ts-icon icon="bookmark" outline class="size-5 text-white" />
        @endif
    </x-ts-button>
</div>
