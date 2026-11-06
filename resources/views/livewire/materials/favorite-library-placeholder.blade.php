<div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8"
    role="status" aria-busy="true" aria-label="Carregando favoritos"
    x-data="{ skeletonFailed: !navigator.onLine }"
    x-init="if (!skeletonFailed) setTimeout(() => skeletonFailed = true, 15000)"
    x-on:offline.window="skeletonFailed = true"
    x-bind:aria-busy="(!skeletonFailed).toString()">
    <div x-show="!skeletonFailed" class="mb-8 space-y-2">
        <x-skeleton.block class="h-10 w-64 max-w-[75%] rounded-xl" />
        <x-skeleton.line size="sm" class="w-96 max-w-full" />
    </div>

    <div x-show="!skeletonFailed" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @foreach (range(1, 8) as $card)
            <x-materials.card-skeleton />
        @endforeach
    </div>

    <div x-cloak x-show="skeletonFailed" role="alert"
        class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-6 text-sm text-amber-950">
        <p class="font-semibold">Não foi possível carregar os favoritos.</p>
        <button type="button" x-on:click="window.location.reload()"
            class="mt-3 font-semibold text-blue-800 underline">Tentar novamente</button>
    </div>

    <span x-show="!skeletonFailed" class="sr-only">Carregando…</span>
</div>
