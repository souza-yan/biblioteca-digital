<x-guest-layout>
    <main class="flex min-h-screen items-center justify-center px-4 py-8 sm:py-12"
        style="background: linear-gradient(to right, #0051ff, #00234e);">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="px-6 pt-8 text-center sm:px-8">
                <img src="{{ asset('images/logo_caraguatatuba.svg') }}" alt="Prefeitura de Caraguatatuba"
                    class="mx-auto h-16 w-auto max-w-full">
                <h1 class="mt-7 text-2xl font-bold tracking-tight text-blue-800">Defina sua nova senha</h1>
            </div>
            <form id="first-login-password" method="POST" action="{{ route('password.first-login.update') }}" class="grid gap-5 px-6 pb-2 pt-7 sm:px-8">
                @csrf
                <div class="min-w-0">
                    <x-ts-input id="new-password" name="password" type="password" label="Nova senha"
                        placeholder="Digite sua nova senha" autocomplete="new-password" required autofocus />
                </div>
                <div class="min-w-0">
                    <x-ts-input id="confirm-new-password" name="password_confirmation" type="password" label="Confirmar nova senha"
                        placeholder="Repita sua nova senha" autocomplete="new-password" required />
                </div>
            </form>
            <div class="flex flex-col gap-3 bg-white px-6 pb-7 pt-5 sm:px-8">
                <x-ts-button type="submit" form="first-login-password" color="blue" class="min-h-12 w-full justify-center">Salvar senha e continuar</x-ts-button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-3 text-sm font-semibold text-red-700 transition-colors hover:border-red-300 hover:bg-red-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700 sm:px-4">
                        <x-ts-icon icon="arrow-right-on-rectangle" outline class="size-5 shrink-0" />
                        Sair da conta
                    </button>
                </form>
            </div>
        </div>
    </main>
</x-guest-layout>
