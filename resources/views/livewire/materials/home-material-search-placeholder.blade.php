<div class="relative z-20 mt-6" role="status" aria-busy="true" aria-label="Carregando busca de materiais"
    x-data="{ skeletonFailed: !navigator.onLine }"
    x-init="if (!skeletonFailed) setTimeout(() => skeletonFailed = true, 15000)"
    x-on:offline.window="skeletonFailed = true"
    x-bind:aria-busy="(!skeletonFailed).toString()">
    <x-skeleton.block x-show="!skeletonFailed" class="h-12 w-full rounded-xl" />
    <div x-cloak x-show="skeletonFailed" role="alert"
        class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
        Não foi possível carregar a busca.
        <button type="button" x-on:click="window.location.reload()"
            class="ml-1 font-semibold text-blue-800 underline">Tentar novamente</button>
    </div>
    <span x-show="!skeletonFailed" class="sr-only">Carregando…</span>
</div>
