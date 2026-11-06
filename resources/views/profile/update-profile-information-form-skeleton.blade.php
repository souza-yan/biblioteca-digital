{{-- Manter em sincronia com profile/update-profile-information-form.blade.php. --}}
<div class="flex h-full min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    role="status" aria-busy="true" aria-label="Carregando dados do perfil"
    x-data="{ skeletonFailed: !navigator.onLine }"
    x-init="if (!skeletonFailed) setTimeout(() => skeletonFailed = true, 15000)"
    x-on:offline.window="skeletonFailed = true"
    x-bind:aria-busy="(!skeletonFailed).toString()">
    <div x-show="!skeletonFailed" aria-hidden="true">
        <div class="p-5 sm:p-6">
            <div class="grid grid-cols-[auto_minmax(0,1fr)] items-center gap-x-4 gap-y-5 sm:gap-x-5 lg:grid-cols-[auto_minmax(0,1fr)_auto]">
                <x-skeleton.circle class="!size-20 sm:!size-24" />

                <div class="min-w-0 space-y-3">
                    <x-skeleton.line size="lg" class="w-52 max-w-full" />
                    <x-skeleton.line size="sm" class="w-64 max-w-full" />
                </div>

                <div class="col-span-2 flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:gap-4 sm:p-4 lg:col-span-1 lg:w-auto lg:min-w-72">
                    <x-skeleton.button class="!size-14 !rounded-xl" />
                    <div class="min-w-0 flex-1 space-y-2">
                        <x-skeleton.line size="sm" class="w-28" />
                        <x-skeleton.line size="xs" class="w-36 max-w-full" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex min-w-0 flex-1 flex-col border-t border-slate-200 p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <x-skeleton.block class="size-12 shrink-0 rounded-xl" />
                <div class="min-w-0 flex-1 space-y-2 pt-1">
                    <x-skeleton.line class="w-44" />
                    <x-skeleton.line size="sm" class="w-72 max-w-full" />
                </div>
            </div>

            <div class="mt-5 flex flex-1 flex-col gap-5">
                @foreach (range(1, 2) as $field)
                    <div class="space-y-2">
                        <x-skeleton.line size="sm" class="w-16" />
                        <x-skeleton.block class="h-10 rounded-lg" />
                    </div>
                @endforeach

                <div class="mt-auto flex justify-end pt-2">
                    <x-skeleton.button class="!h-10 !w-40" />
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="skeletonFailed" role="alert"
        class="m-5 rounded-xl border border-amber-200 bg-amber-50 px-5 py-6 text-sm text-amber-950 sm:m-6">
        <p class="font-semibold">Não foi possível carregar os dados do perfil.</p>
        <button type="button" x-on:click="window.location.reload()"
            class="mt-3 font-semibold text-blue-800 underline">Tentar novamente</button>
    </div>

    <span x-show="!skeletonFailed" class="sr-only">Carregando dados do perfil…</span>
</div>
