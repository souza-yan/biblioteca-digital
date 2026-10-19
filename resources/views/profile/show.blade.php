<x-app-layout>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <header>
            <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Meu perfil</h1>
            <p class="mt-2 text-base text-slate-500">Gerencie seus dados, sua senha e as sessões da sua conta.</p>
        </header>
        <div class="grid grid-cols-1 items-stretch gap-6 xl:grid-cols-2">

            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                @livewire('profile.update-password-form')
            @endif

            <div class="min-w-0 xl:col-span-2">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

        </div>
    </div>
</x-app-layout>
