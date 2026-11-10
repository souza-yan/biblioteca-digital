<x-guest-layout>
    <main class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-bold text-blue-800">Defina sua nova senha</h1>
            <form method="POST" action="{{ route('password.first-login.update') }}" class="mt-6 space-y-5">
                @csrf
                <div>
                    <x-ts-input id="new-password" name="password" type="password" label="Nova senha"
                        autocomplete="new-password" required autofocus />
                </div>
                <x-ts-input id="confirm-new-password" name="password_confirmation" type="password" label="Confirmar nova senha"
                    autocomplete="new-password" required />
                <x-ts-button type="submit" color="blue" class="w-full justify-center">Salvar senha e continuar</x-ts-button>
            </form>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Sair da conta</button>
            </form>
        </div>
    </main>
</x-guest-layout>
