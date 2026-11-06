{{-- Manter em sincronia com profile/logout-other-browser-sessions-form.blade.php. --}}
<div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
    role="status" aria-busy="true" aria-label="Carregando sessões do navegador"
    x-data="{ skeletonFailed: !navigator.onLine }"
    x-init="if (!skeletonFailed) setTimeout(() => skeletonFailed = true, 15000)"
    x-on:offline.window="skeletonFailed = true"
    x-bind:aria-busy="(!skeletonFailed).toString()">
    <div x-show="!skeletonFailed" aria-hidden="true">
        <div class="flex items-start gap-4">
            <x-skeleton.block class="size-12 shrink-0 rounded-xl" />
            <div class="min-w-0 flex-1 space-y-2 pt-1">
                <x-skeleton.line class="w-48" />
                <x-skeleton.line size="sm" class="w-80 max-w-full" />
            </div>
        </div>

        <div class="mt-5 space-y-2">
            <x-skeleton.line size="sm" class="w-full" />
            <x-skeleton.line size="sm" class="w-5/6" />
        </div>

        <div class="mt-5 space-y-3">
            @foreach (range(1, 2) as $session)
                <div class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3">
                    <x-skeleton.block class="size-8 shrink-0 rounded-lg" />
                    <div class="min-w-0 flex-1 space-y-2">
                        <x-skeleton.line size="sm" class="w-44 max-w-full" />
                        <x-skeleton.line size="xs" class="w-56 max-w-full" />
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5 flex justify-end">
            <x-skeleton.button class="!size-12" />
        </div>
    </div>

    <div x-cloak x-show="skeletonFailed" role="alert"
        class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-6 text-sm text-amber-950">
        <p class="font-semibold">Não foi possível carregar as sessões do navegador.</p>
        <button type="button" x-on:click="window.location.reload()"
            class="mt-3 font-semibold text-blue-800 underline">Tentar novamente</button>
    </div>

    <span x-show="!skeletonFailed" class="sr-only">Carregando sessões do navegador…</span>
</div>
