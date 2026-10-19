<div class="flex h-full min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

    {{-- Resumo da conta --}}
    <div x-data="{ photoPreview: null }" class="p-5 sm:p-6">
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <input type="file" id="photo" class="hidden" wire:model.live="photo" x-ref="photo"
                x-on:change="
                        const file = $refs.photo.files[0];
                        if (! file) return;
                        const reader = new FileReader();
                        reader.onload = (e) => { photoPreview = e.target.result; };
                        reader.readAsDataURL(file);
                   " />
        @endif

        <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center">
            <div class="shrink-0">
                <img x-show="! photoPreview" src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}"
                    class="size-16 rounded-full object-cover ring-2 ring-blue-100">

                <span x-show="photoPreview" style="display: none;"
                    class="block size-16 rounded-full bg-cover bg-center bg-no-repeat ring-2 ring-blue-100"
                    x-bind:style="'background-image: url(\'' + photoPreview + '\');'"></span>
            </div>

            <div class="min-w-0 max-w-full">
                <h2 class="break-words text-2xl font-bold text-gray-900">{{ $this->user->name }}</h2>
                <p class="break-all text-sm text-gray-500">{{ $this->user->email }}</p>
            </div>
        </div>

        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button type="button" x-on:click.prevent="$refs.photo.click()" aria-label="Alterar foto" title="Alterar foto"
                    class="inline-flex size-12 items-center justify-center rounded-lg border border-gray-200 bg-white text-blue-900 hover:bg-gray-50">
                    <x-ts-icon icon="camera" outline class="size-7" />
                </button>

                @if ($this->user->profile_photo_path)
                    <button type="button" wire:click="deleteProfilePhoto" aria-label="Remover foto" title="Remover foto"
                        class="inline-flex size-12 items-center justify-center rounded-lg border border-gray-200 bg-white text-red-600 hover:bg-red-50">
                        <x-ts-icon icon="trash" outline class="size-7" />
                    </button>
                @endif

                <span class="text-xs text-gray-400">JPG ou PNG. Tamanho máximo de 1 MB.</span>
            </div>

            <p x-show="photoPreview" style="display: none;" class="mt-3 text-sm text-blue-900">
                Clique em <strong>Salvar alterações</strong> para confirmar a nova foto.
            </p>

            <x-input-error for="photo" class="mt-2" />
        @endif
    </div>

    {{-- Informações do perfil --}}
    <div class="flex min-w-0 flex-1 flex-col border-t border-slate-200 p-5 sm:p-6">
        <div class="flex items-start gap-4">
            <div class="flex items-center justify-center size-12 shrink-0 rounded-xl bg-blue-50 text-blue-900">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">Informações do perfil</h3>
                <p class="text-sm text-gray-500">Atualize seus dados pessoais e informações de contato.</p>
            </div>
        </div>

        <form wire:submit="updateProfileInformation" class="mt-5 flex flex-1 flex-col gap-5">
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-800">Nome</label>
                <div class="relative mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="absolute left-3 top-1/2 -translate-y-1/2 size-5 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <input id="name" type="text" wire:model="state.name" required autocomplete="name"
                        class="block w-full pl-10 border-gray-300 rounded-lg shadow-sm text-sm focus:border-blue-800 focus:ring-blue-800">
                </div>
                <x-input-error for="name" class="mt-2" />
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-800">E-mail</label>
                <div class="relative mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="absolute left-3 top-1/2 -translate-y-1/2 size-5 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <input id="email" type="email" wire:model="state.email" required autocomplete="username"
                        class="block w-full pl-10 border-gray-300 rounded-lg shadow-sm text-sm focus:border-blue-800 focus:ring-blue-800">
                </div>
                <x-input-error for="email" class="mt-2" />

                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) &&
                        !$this->user->hasVerifiedEmail())
                    <p class="text-sm mt-2 text-gray-600">
                        Seu e-mail ainda não foi verificado.
                        <button type="button" class="inline-flex size-12 items-center justify-center rounded text-gray-700 hover:bg-gray-100" aria-label="Reenviar e-mail de verificação" title="Reenviar e-mail de verificação"
                            wire:click.prevent="sendEmailVerification">
                            <x-ts-icon icon="envelope" outline class="size-7" />
                        </button>
                    </p>

                    @if ($this->verificationLinkSent)
                        <p class="mt-2 font-medium text-sm text-green-600">
                            Um novo link de verificação foi enviado para o seu e-mail.
                        </p>
                    @endif
                @endif
            </div>

            <div class="mt-auto flex flex-wrap items-center justify-end gap-3 pt-2">
                <x-action-message class="me-1" on="saved">
                    <span
                        class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-semibold text-green-700 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            class="size-5 shrink-0" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>

                        Alterações salvas com sucesso!
                    </span>
                </x-action-message>

                <button type="submit" wire:loading.attr="disabled" wire:target="photo"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="size-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>

                    Salvar alterações
                </button>
            </div>
        </form>
    </div>
</div>
