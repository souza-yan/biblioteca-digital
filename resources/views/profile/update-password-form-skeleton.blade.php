{{-- Manter em sincronia com profile/update-password-form.blade.php. --}}
<div class="flex h-full min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
    role="status" aria-busy="true" aria-label="Carregando segurança da conta"
    x-data="{ skeletonFailed: !navigator.onLine }"
    x-init="if (!skeletonFailed) setTimeout(() => skeletonFailed = true, 15000)"
    x-on:offline.window="skeletonFailed = true"
    x-bind:aria-busy="(!skeletonFailed).toString()">
    <div x-show="!skeletonFailed" class="flex h-full flex-col" aria-hidden="true">
        <div class="flex items-start gap-4">
            <x-skeleton.block class="size-12 shrink-0 rounded-xl" />
            <x-skeleton.line class="mt-2 w-44" />
        </div>

        <div class="mt-5 flex flex-1 flex-col gap-5">
            @foreach (range(1, 3) as $field)
                <div class="space-y-2">
                    <x-skeleton.line size="sm" class="w-28" />
                    <x-skeleton.block class="h-12 rounded-lg" />

                    @if ($field === 2)
                        <div class="grid grid-cols-4 gap-2 pt-1">
                            @foreach (range(1, 4) as $strength)
                                <x-skeleton.block class="h-1 rounded-full" />
                            @endforeach
                        </div>
                        <x-skeleton.line size="xs" class="w-32" />
                    @endif
                </div>
            @endforeach

            <div class="mt-auto flex justify-end gap-3 pt-2">
                <x-skeleton.button class="!size-10" />
                <x-skeleton.button class="!h-10 !w-40" />
            </div>
        </div>
    </div>

    <div x-cloak x-show="skeletonFailed" role="alert"
        class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-6 text-sm text-amber-950">
        <p class="font-semibold">Não foi possível carregar a segurança da conta.</p>
        <button type="button" x-on:click="window.location.reload()"
            class="mt-3 font-semibold text-blue-800 underline">Tentar novamente</button>
    </div>

    <span x-show="!skeletonFailed" class="sr-only">Carregando segurança da conta…</span>
</div>
