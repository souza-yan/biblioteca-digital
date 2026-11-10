<div class="flex h-full min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="p-5 sm:p-6">
        <div class="grid grid-cols-[auto_minmax(0,1fr)] items-center gap-x-4 gap-y-4 sm:gap-x-5">
            <x-skeleton.block class="size-20 shrink-0 rounded-full sm:size-28" />
            <div class="min-w-0 max-w-full flex-1">
                <x-skeleton.text class="text-xl font-bold leading-snug [overflow-wrap:anywhere] sm:text-2xl">{{ $this->user->name }}</x-skeleton.text>
                <div class="mt-2 text-sm [overflow-wrap:anywhere] sm:text-base">
                    <x-skeleton.text>{{ $this->user->email }}</x-skeleton.text>
                </div>
            </div>
            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <div class="col-span-2 flex min-w-0 w-full items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:gap-4 sm:p-4">
                    <x-skeleton.block class="size-12 shrink-0 rounded-xl sm:size-14" />
                    <div class="min-w-0 flex-1 text-sm leading-6">
                        <x-skeleton.text class="font-semibold">{{ strtoupper(implode(' ou ', config('profile.photo.allowed_mimes'))) }}</x-skeleton.text>
                        <x-skeleton.text>Tamanho máximo de {{ number_format(config('profile.photo.max_size_kilobytes') / 1024, 2, ',', '.') }} MB</x-skeleton.text>
                    </div>
                    @if ($this->user->profile_photo_path)
                        <x-skeleton.block class="size-12 shrink-0 rounded-xl" />
                    @endif
                </div>
            @endif
        </div>
    </div>
    <div class="flex min-w-0 flex-1 flex-col border-t border-slate-200 p-5 sm:p-6">
        <div class="flex items-start gap-4">
            <x-skeleton.block class="size-12 shrink-0 rounded-xl" />
            <div>
                <x-skeleton.text class="text-lg font-bold">Informações do perfil</x-skeleton.text>
                <x-skeleton.text class="text-sm">Atualize seus dados pessoais e informações de contato.</x-skeleton.text>
            </div>
        </div>
        <div class="mt-5 flex flex-1 flex-col gap-5">
            <div>
                <x-skeleton.text class="w-fit text-sm font-semibold">Nome</x-skeleton.text>
                <x-skeleton.block class="mt-1 h-[38px] w-full rounded-lg" />
            </div>
            <div>
                <x-skeleton.text class="w-fit text-sm font-semibold">E-mail</x-skeleton.text>
                <x-skeleton.block class="mt-1 h-[38px] w-full rounded-lg" />
                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                    <div class="mt-2 text-sm"><x-skeleton.text class="inline-block">Seu e-mail ainda não foi verificado.</x-skeleton.text> <x-skeleton.block class="inline-block size-12 rounded align-middle" /></div>
                @endif
            </div>
            <div class="mt-auto flex flex-wrap items-center justify-end gap-3 pt-2">
                <div class="flex items-center gap-2 rounded-lg bg-slate-200 px-5 py-2.5 text-sm font-bold"><span class="size-4"></span><span class="invisible">Salvar alterações</span></div>
            </div>
        </div>
    </div>
</div>
