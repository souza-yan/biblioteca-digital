<div class="flex h-full min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    <div class="flex items-start gap-4">
        <x-skeleton.block class="size-12 shrink-0 rounded-xl" />
        <x-skeleton.text class="text-lg font-bold">Segurança da conta</x-skeleton.text>
    </div>
    <div class="mt-5 flex flex-1 flex-col gap-5">
        @foreach (['Senha atual', 'Nova senha', 'Confirmar senha'] as $label)
            <div>
                <x-skeleton.text class="w-fit text-sm font-semibold">{{ $label }}</x-skeleton.text>
                <x-skeleton.block class="mt-1 h-12 w-full rounded-lg" />
                @if ($loop->index === 1)
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach (range(1, 4) as $segment)
                            <x-skeleton.block class="h-1 rounded-full" />
                        @endforeach
                    </div>
                    <x-skeleton.text class="mt-1 w-fit text-xs">Força da senha: <span class="font-semibold">muito fraca</span></x-skeleton.text>
                @endif
            </div>
        @endforeach
        <div class="mt-auto flex flex-wrap items-center justify-end gap-3 pt-2">
            <x-skeleton.block class="size-11 rounded-lg" />
            <div class="flex items-center gap-2 rounded-lg bg-slate-200 px-5 py-2.5 text-sm font-bold"><span class="size-4"></span><span class="invisible">Atualizar senha</span></div>
        </div>
    </div>
</div>
