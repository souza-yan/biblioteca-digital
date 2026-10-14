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
                {{-- Erros de validação --}}
                <x-validation-errors class="mb-5" />

                {{-- Mensagem de status --}}
                @if (session('status'))
                    <div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- E-mail --}}
                    <div>
                        <x-ts-input id="email" name="email" label="E-mail" type="email"
                            placeholder="seu@email.com" value="{{ old('email') }}" autocomplete="username" required
                            autofocus class="w-full" />
                    </div>

                    {{-- Senha --}}
                    <div>
                        <x-ts-input id="password" name="password" label="Senha" type="password"
                            placeholder="Digite sua senha" autocomplete="current-password" required class="w-full" />
                    </div>

                    {{-- Lembrar usuário --}}
                    <div class="flex items-center">
                        <x-ts-checkbox id="remember_me" name="remember" label="Lembrar de mim" />
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
