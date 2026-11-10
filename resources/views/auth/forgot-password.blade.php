<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            @if (in_array(config('mail.default'), ['log', 'array'], true))
                Solicite a redefinição da senha à administração do sistema. Professores podem pedir encaminhamento à gestão da escola.
            @else
                Informe seu e-mail de cadastro para receber um link de redefinição de senha.
            @endif
        </div>

        @unless (in_array(config('mail.default'), ['log', 'array'], true))
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="block">
                <x-label for="email" value="{{ __('E-mail') }}" />
                <x-input
                    id="email"
                    class="block mt-1 w-full"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Enviar link de redefinição de senha') }}
                </x-button>
            </div>
        </form>
        @endunless
        <a href="{{ route('login') }}" class="mt-4 inline-flex min-h-11 items-center rounded text-sm text-blue-800 underline">Voltar para entrar</a>
    </x-authentication-card>
</x-guest-layout>
