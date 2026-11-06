<div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8"
    role="status" aria-busy="true" aria-label="Carregando detalhes do material"
    x-data="{ skeletonFailed: !navigator.onLine }"
    x-init="if (!skeletonFailed) setTimeout(() => skeletonFailed = true, 15000)"
    x-on:offline.window="skeletonFailed = true"
    x-bind:aria-busy="(!skeletonFailed).toString()">
    <div x-show="!skeletonFailed" class="space-y-6 lg:space-y-8">
        <x-materials.detail-summary-skeleton :is-teacher="$isTeacher" :has-current-version="$hasCurrentVersion" />

        @if ($canViewVersionHistory)
            <section class="mt-6 flex flex-col rounded-md border border-gray-200 bg-white p-4 shadow-sm sm:p-6"
                aria-hidden="true">
                <x-skeleton.line size="lg" class="w-24" />
                <div class="order-3 mt-4 sm:order-2">
                    <x-materials.version-history-skeleton />
                </div>
                @if ($canManageVersions)
                    <x-materials.version-form-skeleton />
                @endif
            </section>
        @endif
    </div>

    <div x-cloak x-show="skeletonFailed" role="alert"
        class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-6 text-sm text-amber-950">
        <p class="font-semibold">Não foi possível carregar os detalhes do material.</p>
        <button type="button" x-on:click="window.location.reload()"
            class="mt-3 font-semibold text-blue-800 underline">Tentar novamente</button>
    </div>

    <span x-show="!skeletonFailed" class="sr-only">Carregando…</span>
</div>
