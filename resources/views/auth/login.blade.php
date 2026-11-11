<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4 py-10"
        style="background: linear-gradient(to right, #0051ff, #00234e);">
        <div class="w-full max-w-md">
            {{-- Cabeçalho --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-8 text-center">
                    <div class="flex justify-center mb-5">
                        <img src="{{ asset('images/logo_caraguatatuba.svg') }}" alt="Logo Prefeitura de Caraguatatuba"
                            class="h-16 w-auto">
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-blue-800">
                        Biblioteca Digital de Robótica
                    </h1>
                </div>
                <form method="POST" action="{{ route('login') }}" class="space-y-5"
                    x-data="{
                        identifierLifetime: {{ config('accounts.remember_identifier_days') }} * 24 * 60 * 60 * 1000,
                        restoreIdentifier() {
                            try {
                                const saved = localStorage.getItem('biblioteca.remembered-login');
                                if (!saved) return;
                                let record;
                                try { record = JSON.parse(saved); } catch (error) { this.clearIdentifier(false); return; }
                                if (!record || typeof record.identifier !== 'string' || !Number.isFinite(record.expiresAt) || record.expiresAt <= Date.now()) {
                                    this.clearIdentifier(false);
                                    return;
                                }
                                const login = this.$el.elements.namedItem('login');
                                if (!login.value) login.value = record.identifier;
                                this.$el.elements.namedItem('remember').checked = true;
                            } catch (error) {}
                        },
                        saveIdentifier() {
                            try {
                                if (this.$el.elements.namedItem('remember').checked) {
                                    localStorage.setItem('biblioteca.remembered-login', JSON.stringify({
                                        identifier: this.$el.elements.namedItem('login').value.trim(),
                                        expiresAt: Date.now() + this.identifierLifetime,
                                    }));
                                } else {
                                    localStorage.removeItem('biblioteca.remembered-login');
                                }
                            } catch (error) {}
                        },
                        forgetIdentifier() {
                            if (this.$el.elements.namedItem('remember').checked) return;
                            try {
                                localStorage.removeItem('biblioteca.remembered-login');
                            } catch (error) {}
                        },
                        clearIdentifier(clearField = true) {
                            try { localStorage.removeItem('biblioteca.remembered-login'); } catch (error) {}
                            this.$el.elements.namedItem('remember').checked = false;
                            if (clearField) this.$el.elements.namedItem('login').value = '';
                        }
                    }"
                    x-init="$nextTick(() => restoreIdentifier())"
                    x-on:submit="saveIdentifier()">
                    @csrf

                    {{-- Identificação da conta --}}
                    <div>
                        <x-ts-input id="login" name="login" label="CPF ou e-mail" type="text"
                            placeholder="CPF ou e-mail do administrador" value="{{ old('login') }}" autocomplete="username" required
                            autofocus class="w-full" />
                    </div>

                    {{-- Senha --}}
                    <div>
                        <x-ts-input id="password" name="password" label="Senha" type="password"
                            placeholder="Digite sua senha" autocomplete="current-password" required class="w-full" />
                    </div>

                    {{-- Lembrar usuário --}}
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <x-ts-checkbox id="remember_me" name="remember" label="Lembrar de mim"
                            x-on:change="forgetIdentifier()" />
                    </div>

                    {{-- Entrar --}}
                    <div class="pt-2">
                        <x-ts-button type="submit" color="blue" class="w-full justify-center">
                            Entrar
                        </x-ts-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
