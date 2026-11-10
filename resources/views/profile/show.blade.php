<x-app-layout>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <header>
            <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Meu perfil</h1>
        </header>
        <div class="grid grid-cols-1 items-stretch gap-6 xl:grid-cols-2">

            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                @livewire('profile.update-password-form')
            @endif

            <div class="min-w-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            <x-skeleton.gate name="profile-security" label="Carregando informações de segurança"
                class="h-full min-w-0 [&>div]:h-full [&>div>div]:h-full">
                <x-slot:skeleton>
                    <div class="h-full min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex items-start gap-4">
                            <x-skeleton.block class="size-12 shrink-0 rounded-xl" />
                            <div class="min-w-0">
                                <x-skeleton.text class="w-fit text-lg font-bold">Sistema seguro</x-skeleton.text>
                                <x-skeleton.text class="mt-1 text-sm">Sua conta é protegida por autenticação e controle de sessões.</x-skeleton.text>
                            </div>
                        </div>
                        <div class="mt-5 space-y-3 text-sm">
                            <div class="flex items-start gap-2">
                                <x-skeleton.block class="mt-1 size-3 shrink-0 rounded" />
                                <x-skeleton.text>Senhas armazenadas com segurança.</x-skeleton.text>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-skeleton.block class="mt-1 size-3 shrink-0 rounded" />
                                <x-skeleton.text>Sessões podem ser encerradas a qualquer momento.</x-skeleton.text>
                            </div>
                        </div>
                    </div>
                </x-slot:skeleton>
            <section aria-labelledby="system-security-title"
                class="h-full min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start gap-4">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-white text-emerald-700 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75m5.25 2.25c0 5.591-3.824 7.743-7.5 9-3.676-1.257-7.5-3.409-7.5-9V5.25L12.75 3l7.5 2.25V12Z" />
                        </svg>
                    </span>
                    <div>
                        <h2 id="system-security-title" class="text-lg font-bold text-emerald-950">Sistema seguro</h2>
                        <p class="mt-1 text-sm text-emerald-900">Sua conta é protegida por autenticação e controle de sessões.</p>
                    </div>
                </div>

                <ul class="mt-5 space-y-3 text-sm text-emerald-950">
                    <li class="flex gap-2">
                        <span aria-hidden="true">✓</span>
                        Senhas armazenadas com segurança.
                    </li>
                    <li class="flex gap-2">
                        <span aria-hidden="true">✓</span>
                        Sessões podem ser encerradas a qualquer momento.
                    </li>
                </ul>
            </section>
            </x-skeleton.gate>

        </div>
    </div>
</x-app-layout>
