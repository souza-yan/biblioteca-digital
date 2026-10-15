<div class="bg-white shadow rounded-2xl p-6 flex flex-col lg:col-start-2 lg:row-start-2" x-data="{
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
                <input id="current_password" x-bind:type="showCurrent ? 'text' : 'password'"
                    wire:model="state.current_password" autocomplete="current-password"
                    placeholder="Digite sua senha atual"
                    class="block w-full pl-10 pr-10 border-gray-300 rounded-lg shadow-sm text-sm focus:border-blue-800 focus:ring-blue-800">
                <button type="button" x-on:click="showCurrent = ! showCurrent"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                    <svg x-show="! showCurrent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="showCurrent" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
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
                <input id="password" x-bind:type="showNew ? 'text' : 'password'" wire:model="state.password"
                    x-on:input="pw = $event.target.value" autocomplete="new-password"
                    placeholder="Digite sua nova senha"
                    class="block w-full pl-10 pr-10 border-gray-300 rounded-lg shadow-sm text-sm focus:border-blue-800 focus:ring-blue-800">
                <button type="button" x-on:click="showNew = ! showNew"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                    <svg x-show="! showNew" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="showNew" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
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
                <input id="password_confirmation" x-bind:type="showConfirm ? 'text' : 'password'"
                    wire:model="state.password_confirmation" autocomplete="new-password"
                    placeholder="Digite novamente sua nova senha"
                    class="block w-full pl-10 pr-10 border-gray-300 rounded-lg shadow-sm text-sm focus:border-blue-800 focus:ring-blue-800">
                <button type="button" x-on:click="showConfirm = ! showConfirm"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                    <svg x-show="! showConfirm" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="showConfirm" style="display: none;" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            <x-input-error for="password_confirmation" class="mt-2" />
        </div>

        <div class="mt-auto flex items-center justify-end pt-2"> <x-action-message class="me-3"
                on="saved">Senha atualizada.</x-action-message>

            <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-blue-900 rounded-lg hover:bg-blue-800 disabled:opacity-50"
                wire:loading.attr="disabled">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                Atualizar senha
            </button>
        </div>
    </form>
</div>
