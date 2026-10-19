```blade
<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div x-data="{ recovery: false }">
            <div class="mb-4 text-sm text-gray-600" x-show="! recovery">
                {{ __('Confirme o acesso à sua conta digitando o código de autenticação fornecido pelo aplicativo autenticador.') }}
            </div>

            <div class="mb-4 text-sm text-gray-600" x-cloak x-show="recovery">
                {{ __('Confirme o acesso à sua conta digitando um dos seus códigos de recuperação.') }}
            </div>

            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('two-factor.login') }}">
                @csrf

                <div class="mt-4" x-show="! recovery">
                    <x-label for="code" value="{{ __('Código de autenticação') }}" />
                    <x-input
                        id="code"
                        class="block mt-1 w-full"
                        type="text"
                        inputmode="numeric"
                        name="code"
                        autofocus
                        x-ref="code"
                        autocomplete="one-time-code"
                    />
                </div>

                <div class="mt-4" x-cloak x-show="recovery">
                    <x-label for="recovery_code" value="{{ __('Código de recuperação') }}" />
                    <x-input
                        id="recovery_code"
                        class="block mt-1 w-full"
                        type="text"
                        name="recovery_code"
                        x-ref="recovery_code"
                        autocomplete="one-time-code"
                    />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button
                        type="button"
                        aria-label="Usar código de recuperação" title="Usar código de recuperação"
                        class="inline-flex size-12 items-center justify-center rounded text-gray-600 hover:bg-gray-100"
                        x-show="! recovery"
                        x-on:click="
                            recovery = true;
                            $nextTick(() => { $refs.recovery_code.focus() })
                        "
                    >
                        <x-ts-icon icon="key" outline class="size-7" />
                    </button>

                    <button
                        type="button"
                        aria-label="Usar código de autenticação" title="Usar código de autenticação"
                        class="inline-flex size-12 items-center justify-center rounded text-gray-600 hover:bg-gray-100"
                        x-cloak
                        x-show="recovery"
                        x-on:click="
                            recovery = false;
                            $nextTick(() => { $refs.code.focus() })
                        "
                    >
                        <x-ts-icon icon="device-phone-mobile" outline class="size-7" />
                    </button>

                    <x-button class="ms-4">
                        {{ __('Entrar') }}
                    </x-button>
                </div>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
```
