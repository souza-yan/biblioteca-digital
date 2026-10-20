<div class="flex h-full min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" x-data="{
    showCurrent: false,
    showNew: false,
    showConfirm: false,
    pw: '',
    get score() {
        let s = 0;
        if (this.pw.length >= 8) s++;
        if (/[a-z]/.test(this.pw) && /[A-Z]/.test(this.pw)) s++;
        if (/\d/.test(this.pw)) s++;
        if (/[^A-Za-z0-9]/.test(this.pw)) s++;
        return s;
    },
    get label() { return ['muito fraca', 'fraca', 'razoável', 'boa', 'forte'][this.score]; }
}"
    x-on:saved.window="pw = ''">

    <div class="flex items-start gap-4">
        <div class="flex items-center justify-center size-12 shrink-0 rounded-xl bg-blue-50 text-blue-900">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        <div>
            <h3 class="text-lg font-bold text-gray-900">Segurança da conta</h3>
            <p class="text-sm text-gray-500">Altere sua senha para manter sua conta segura.</p>
        </div>
    </div>

    <form wire:submit="updatePassword" class="mt-5 flex flex-1 flex-col gap-5">

        {{-- Senha atual --}}
        <div>
            <label for="current_password" class="block text-sm font-semibold text-gray-800">Senha atual</label>
            <div class="relative mt-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="absolute left-3 top-1/2 -translate-y-1/2 size-5 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <input id="current_password" type="password" x-bind:type="showCurrent ? 'text' : 'password'"
                    wire:model="state.current_password" autocomplete="current-password"
                    placeholder="Digite sua senha atual"
                    class="block h-12 w-full pl-10 pr-14 border-gray-300 rounded-lg shadow-sm text-sm focus:border-blue-800 focus:ring-blue-800">
                <button type="button" x-on:click="showCurrent = ! showCurrent" x-bind:aria-label="showCurrent ? 'Ocultar senha atual' : 'Mostrar senha atual'" x-bind:title="showCurrent ? 'Ocultar senha' : 'Mostrar senha'"
                    class="absolute right-1 top-1/2 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                    <x-ts-icon x-show="! showCurrent" icon="eye" outline class="size-6" />
                    <x-ts-icon x-show="showCurrent" icon="eye-slash" outline class="size-6" />
                </button>
            </div>
            <x-input-error for="current_password" class="mt-2" />
        </div>

        {{-- Nova senha --}}
        <div>
            <label for="password" class="block text-sm font-semibold text-gray-800">Nova senha</label>
            <div class="relative mt-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="absolute left-3 top-1/2 -translate-y-1/2 size-5 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <input id="password" type="password" x-bind:type="showNew ? 'text' : 'password'" wire:model="state.password"
                    x-on:input="pw = $event.target.value" autocomplete="new-password"
                    placeholder="Digite sua nova senha"
                    class="block h-12 w-full pl-10 pr-14 border-gray-300 rounded-lg shadow-sm text-sm focus:border-blue-800 focus:ring-blue-800">
                <button type="button" x-on:click="showNew = ! showNew" x-bind:aria-label="showNew ? 'Ocultar nova senha' : 'Mostrar nova senha'" x-bind:title="showNew ? 'Ocultar senha' : 'Mostrar senha'"
                    class="absolute right-1 top-1/2 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                    <x-ts-icon x-show="! showNew" icon="eye" outline class="size-6" />
                    <x-ts-icon x-show="showNew" icon="eye-slash" outline class="size-6" />
                </button>
            </div>
            <x-input-error for="password" class="mt-2" />

            {{-- Medidor de força --}}
            <div class="mt-3 grid grid-cols-4 gap-2">
                <div class="h-1 rounded-full"
                    x-bind:class="score >= 1 ? (score <= 1 ? 'bg-red-500' : score == 2 ? 'bg-amber-500' : score == 3 ? 'bg-lime-500' :
                        'bg-emerald-500') : 'bg-gray-200'">
                </div>
                <div class="h-1 rounded-full"
                    x-bind:class="score >= 2 ? (score == 2 ? 'bg-amber-500' : score == 3 ? 'bg-lime-500' : 'bg-emerald-500') :
                        'bg-gray-200'">
                </div>
                <div class="h-1 rounded-full"
                    x-bind:class="score >= 3 ? (score == 3 ? 'bg-lime-500' : 'bg-emerald-500') : 'bg-gray-200'"></div>
                <div class="h-1 rounded-full" x-bind:class="score >= 4 ? 'bg-emerald-500' : 'bg-gray-200'"></div>
            </div>
            <p class="mt-1 text-xs text-gray-500">
                Força da senha:
                <span class="font-semibold" x-text="label"
                    x-bind:class="score <= 1 ? 'text-red-500' : score == 2 ? 'text-amber-500' : score == 3 ? 'text-lime-600' :
                        'text-emerald-600'"></span>
            </p>
        </div>

        {{-- Confirmar senha --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-800">Confirmar
                senha</label>
            <div class="relative mt-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="absolute left-3 top-1/2 -translate-y-1/2 size-5 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <input id="password_confirmation" type="password" x-bind:type="showConfirm ? 'text' : 'password'"
                    wire:model="state.password_confirmation" autocomplete="new-password"
                    placeholder="Digite novamente sua nova senha"
                    class="block h-12 w-full pl-10 pr-14 border-gray-300 rounded-lg shadow-sm text-sm focus:border-blue-800 focus:ring-blue-800">
                <button type="button" x-on:click="showConfirm = ! showConfirm" x-bind:aria-label="showConfirm ? 'Ocultar confirmação da senha' : 'Mostrar confirmação da senha'" x-bind:title="showConfirm ? 'Ocultar senha' : 'Mostrar senha'"
                    class="absolute right-1 top-1/2 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                    <x-ts-icon x-show="! showConfirm" icon="eye" outline class="size-6" />
                    <x-ts-icon x-show="showConfirm" icon="eye-slash" outline class="size-6" />
                </button>
            </div>
            <x-input-error for="password_confirmation" class="mt-2" />
        </div>

        <div class="mt-auto flex flex-wrap items-center justify-end gap-3 pt-2">
            <x-saved-toast title="Senha atualizada!" message="Sua nova senha foi salva com sucesso." />

            @livewire('profile.reset-default-password')

            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>

                Atualizar senha
            </button>
        </div>
    </form>
</div>
