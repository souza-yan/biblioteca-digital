<div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    <div class="flex items-start gap-4">
        <div class="flex items-center justify-center size-12 shrink-0 rounded-xl bg-blue-50 text-blue-900">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
            </svg>
        </div>
        <div>
            <h3 class="text-lg font-bold text-gray-900">Sessões do navegador</h3>
            <p class="text-sm text-gray-500">Gerencie suas sessões ativas em outros navegadores e dispositivos.</p>
        </div>
    </div>

    <p class="mt-5 text-sm text-gray-600">
        Se necessário, você pode encerrar todas as suas outras sessões em todos os dispositivos. Algumas das sessões
        recentes estão listadas abaixo, mas a lista pode não estar completa. Se suspeitar que sua conta foi
        comprometida, altere também a sua senha.
    </p>

    @if (count($this->sessions) > 0)
        <div class="mt-5 space-y-3">
            @foreach ($this->sessions as $session)
                <div class="flex items-center p-3 bg-gray-50 border border-gray-100 rounded-lg">
                    <div>
                        @if ($session->agent->isDesktop())
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-8 text-gray-500">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-8 text-gray-500">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                            </svg>
                        @endif
                    </div>

                    <div class="ms-3 min-w-0 break-words">
                        <div class="text-sm text-gray-700">
                            {{ $session->agent->platform() ?: 'Desconhecido' }} -
                            {{ $session->agent->browser() ?: 'Desconhecido' }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $session->ip_address }},
                            @if ($session->is_current_device)
                                <span class="text-green-600 font-semibold">Este dispositivo</span>
                            @else
                                Último acesso {{ $session->last_active }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-5 flex items-center justify-end">
        <button type="button" wire:click="confirmLogout" wire:loading.attr="disabled" aria-label="Encerrar outras sessões" title="Encerrar outras sessões"
            class="inline-flex size-12 items-center justify-center rounded-lg bg-blue-900 text-white hover:bg-blue-800 disabled:opacity-50">
            <x-ts-icon icon="arrow-right-start-on-rectangle" outline class="size-7" />
        </button>

        <x-saved-toast on="loggedOut" title="Sessões encerradas!"
            message="As outras sessões foram encerradas com sucesso." />
    </div>

    <!-- Modal de confirmação -->
    <x-dialog-modal wire:model.live="confirmingLogout">
        <x-slot name="title">Encerrar outras sessões</x-slot>

        <x-slot name="content">
            Digite sua senha para confirmar que deseja encerrar suas outras sessões em todos os dispositivos.

            <div class="mt-4" x-data="{}"
                x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                <x-input type="password" class="mt-1 block w-3/4" autocomplete="current-password" placeholder="Senha"
                    x-ref="password" wire:model="password" wire:keydown.enter="logoutOtherBrowserSessions" />

                <x-input-error for="password" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingLogout')" wire:loading.attr="disabled" aria-label="Cancelar" title="Cancelar" class="!size-12 !p-0 justify-center">
                <x-ts-icon icon="x-mark" outline class="size-7" />
            </x-secondary-button>

            <button type="button" wire:click="logoutOtherBrowserSessions" wire:loading.attr="disabled"
                class="ms-3 inline-flex items-center px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white bg-blue-900 rounded-md hover:bg-blue-800 disabled:opacity-50">
                Encerrar outras sessões
            </button>
        </x-slot>
    </x-dialog-modal>
</div>
