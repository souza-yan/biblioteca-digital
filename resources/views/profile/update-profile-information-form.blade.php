<div class="contents">

    {{-- Card de resumo + foto --}}
    <div x-data="{ photoPreview: null }" class="bg-white shadow rounded-2xl p-6 lg:col-start-1 lg:row-start-1">
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

        <div class="flex items-center gap-5">
            <div class="shrink-0">
                <img x-show="! photoPreview" src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}"
                    class="size-28 rounded-full object-cover ring-2 ring-blue-100">

                <span x-show="photoPreview" style="display: none;"
                    class="block size-28 rounded-full bg-cover bg-center bg-no-repeat ring-2 ring-blue-100"
                    x-bind:style="'background-image: url(\'' + photoPreview + '\');'"></span>
            </div>

            <div class="min-w-0">
                <h2 class="text-2xl font-bold text-gray-900 truncate">{{ $this->user->name }}</h2>
                <p class="text-sm text-gray-500 truncate">{{ $this->user->email }}</p>

                {{-- Badge opcional, adapte à forma como você guarda o perfil/role:
                <span class="inline-flex items-center mt-2 px-3 py-1 rounded-full bg-blue-50 text-xs font-semibold text-blue-900">Admin</span>
                --}}
            </div>
        </div>

        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button type="button" x-on:click.prevent="$refs.photo.click()"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-blue-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                    </svg>
                    Alterar foto
                </button>

                @if ($this->user->profile_photo_path)
                    <button type="button" wire:click="deleteProfilePhoto"
                        class="px-4 py-2 text-sm font-semibold text-red-600 bg-white border border-gray-200 rounded-lg hover:bg-red-50">
                        Remover foto
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

    {{-- Card de informações --}}
    <div class="bg-white shadow rounded-2xl p-6 flex flex-col lg:col-start-1 lg:row-start-2">
        <div class="mt-auto flex items-center justify-end pt-2">
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

        <form wire:submit="updateProfileInformation" class="mt-5 space-y-5">
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
                        <button type="button" class="underline hover:text-gray-900"
                            wire:click.prevent="sendEmailVerification">
                            Clique aqui para reenviar o e-mail de verificação.
                        </button>
                    </p>

                    @if ($this->verificationLinkSent)
                        <p class="mt-2 font-medium text-sm text-green-600">
                            Um novo link de verificação foi enviado para o seu e-mail.
                        </p>
                    @endif
                @endif
            </div>

            <div class="flex items-center justify-end pt-2">
                <x-action-message class="me-3" on="saved">Salvo.</x-action-message>

                <button type="submit" wire:loading.attr="disabled" wire:target="photo"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-blue-900 rounded-lg hover:bg-blue-800 disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>
</div>
