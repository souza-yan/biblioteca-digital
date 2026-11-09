<div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    <div class="flex items-start gap-4">
        <x-skeleton.block class="size-12 shrink-0 rounded-xl" />
        <div>
            <x-skeleton.text class="text-lg font-bold">Sessões do navegador</x-skeleton.text>
            <x-skeleton.text class="text-sm">Gerencie suas sessões ativas em outros navegadores e dispositivos.</x-skeleton.text>
        </div>
    </div>
    <x-skeleton.text class="mt-5 text-sm">
        Se necessário, você pode encerrar todas as suas outras sessões em todos os dispositivos. Algumas das sessões
        recentes estão listadas abaixo, mas a lista pode não estar completa. Se suspeitar que sua conta foi
        comprometida, altere também a sua senha.
    </x-skeleton.text>
    @if (count($this->sessions) > 0)
        <div class="mt-5 space-y-3">
            @foreach ($this->sessions as $session)
                <div class="flex items-center rounded-lg border border-gray-100 bg-gray-50 p-3">
                    <div><x-skeleton.block class="size-8 rounded" /></div>
                    <div class="ms-3 min-w-0 break-words">
                        <x-skeleton.text class="text-sm">{{ $session->agent->platform() ?: 'Desconhecido' }} - {{ $session->agent->browser() ?: 'Desconhecido' }}</x-skeleton.text>
                        <x-skeleton.text class="text-xs">
                            {{ $session->ip_address }},
                            @if ($session->is_current_device)
                                <span class="font-semibold">Este dispositivo</span>
                            @else
                                Último acesso {{ $session->last_active }}
                            @endif
                        </x-skeleton.text>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="mt-5 flex items-center justify-end"><x-skeleton.block class="size-12 rounded-lg" /></div>
</div>
