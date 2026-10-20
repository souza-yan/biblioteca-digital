<div class="flex h-full min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

    {{-- Resumo da conta --}}
    <div x-data="{ photoPreview: null }"
        x-on:saved.window="photoPreview = null; if ($refs.photo) $refs.photo.value = ''"
        class="p-5 sm:p-6">
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

            <div class="grid grid-cols-[auto_minmax(0,1fr)] items-center gap-x-4 gap-y-5 sm:gap-x-5 lg:grid-cols-[auto_minmax(0,1fr)_auto]">
                <div class="shrink-0">
                    <img x-show="! photoPreview" src="{{ $this->user->profile_photo_url }}"
                        alt="{{ $this->user->name }}" class="size-20 rounded-full object-cover ring-4 ring-blue-100 sm:size-24">

                    <span x-show="photoPreview" style="display: none;"
                        class="block size-20 rounded-full bg-cover bg-center bg-no-repeat ring-4 ring-blue-100 sm:size-24"
                        x-bind:style="'background-image: url(\'' + photoPreview + '\');'"></span>
                </div>

                <div class="min-w-0 max-w-full flex-1">
                    <h2 class="break-words text-2xl font-bold leading-tight text-gray-900 sm:text-3xl">{{ $this->user->name }}</h2>
                    <div class="mt-2 flex flex-col gap-1 text-sm text-gray-500 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-2 sm:text-base">
                        <span class="font-medium text-blue-800">{{ $this->user->role->label() }}</span>
                        <span aria-hidden="true" class="hidden text-gray-300 sm:inline">•</span>
                        <span class="break-all">{{ $this->user->email }}</span>
                    </div>
                </div>

                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="col-span-2 flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:gap-4 sm:p-4 lg:col-span-1 lg:w-auto lg:min-w-72">
                        <button type="button" x-on:click.prevent="$refs.photo.click()" aria-label="Alterar foto"
                            title="Alterar foto"
                            class="inline-flex size-14 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-white text-blue-900 shadow-sm transition hover:border-blue-200 hover:bg-blue-50">
                            <x-ts-icon icon="camera" outline class="size-8" />
                        </button>

                        <span class="min-w-0 flex-1 text-sm leading-6 text-gray-600">
                            <strong class="block font-semibold text-gray-800">JPG ou PNG</strong>
                            Tamanho máximo de 1MB
                        </span>

                        @if ($this->user->profile_photo_path)
                            <button type="button" wire:click="deleteProfilePhoto" aria-label="Remover foto"
                                title="Remover foto"
                                class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl border border-red-100 bg-white text-red-600 transition hover:border-red-200 hover:bg-red-50">
                                <x-ts-icon icon="trash" outline class="size-7" />
                            </button>
                        @endif
                    </div>
                @endif
            </div>

        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
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
                        <button type="button"
                            class="inline-flex size-12 items-center justify-center rounded text-gray-700 hover:bg-gray-100"
                            aria-label="Reenviar e-mail de verificação" title="Reenviar e-mail de verificação"
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
                <x-saved-toast title="Perfil atualizado!" message="Seus dados foram salvos com sucesso." />

                <button type="submit" wire:loading.attr="disabled" wire:target="updateProfileInformation,photo"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="size-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>

                    <span wire:loading.remove wire:target="updateProfileInformation">Salvar alterações</span>
                    <span wire:loading wire:target="updateProfileInformation">Salvando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
