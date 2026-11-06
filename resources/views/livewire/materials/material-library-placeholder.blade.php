<div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8"
    role="status" aria-busy="true" aria-label="Carregando biblioteca de materiais"
    x-data="{ skeletonFailed: !navigator.onLine }"
    x-init="if (!skeletonFailed) setTimeout(() => skeletonFailed = true, 15000)"
    x-on:offline.window="skeletonFailed = true"
    x-bind:aria-busy="(!skeletonFailed).toString()">
    <div x-show="!skeletonFailed" class="mb-8 flex items-center justify-between gap-4">
        <x-skeleton.block class="h-10 w-72 max-w-[75%] rounded-xl" />
        <x-skeleton.block class="h-10 w-24 shrink-0 rounded-lg" />
    </div>

    <div x-show="!skeletonFailed" class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach (range(1, 3) as $filter)
            <div class="space-y-2">
                <x-skeleton.line size="sm" class="w-24" />
                <x-skeleton.block class="h-11 rounded-xl" />
            </div>
        @endforeach
    </div>

    <div x-show="!skeletonFailed" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @foreach (range(1, 8) as $card)
            <x-materials.card-skeleton />
        @endforeach
    </div>

    <div x-cloak x-show="skeletonFailed" role="alert"
        class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-6 text-sm text-amber-950">
        <p class="font-semibold">Não foi possível carregar os materiais.</p>
        <button type="button" x-on:click="window.location.reload()"
            class="mt-3 font-semibold text-blue-800 underline">Tentar novamente</button>
    </div>

    <span x-show="!skeletonFailed" class="sr-only">Carregando…</span>
</div>
