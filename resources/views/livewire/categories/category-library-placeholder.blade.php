@php($isStaff = auth()->user()?->isStaff() ?? false)

<div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8"
    role="status" aria-busy="true" aria-label="Carregando categorias"
    x-data="{ skeletonFailed: !navigator.onLine }"
    x-init="if (!skeletonFailed) setTimeout(() => skeletonFailed = true, 15000)"
    x-on:offline.window="skeletonFailed = true"
    x-bind:aria-busy="(!skeletonFailed).toString()">
    <section x-show="!skeletonFailed" @class([
        'relative isolate overflow-hidden rounded-2xl bg-blue-800 text-white shadow-sm sm:px-9 sm:py-10 lg:px-12 lg:py-12',
        'px-5 py-6' => $isStaff,
        'px-6 py-8' => ! $isStaff,
    ])>
        <x-skeleton.line size="sm" class="w-36 bg-blue-200/50" />
        <x-skeleton.block class="mt-3 h-12 w-56 max-w-[70%] rounded-xl bg-blue-100/90" />
        <x-skeleton.line size="lg" class="mt-4 w-[42rem] max-w-[90%] bg-blue-100/90" />
    </section>

    <section x-show="!skeletonFailed" class="space-y-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div @class(['hidden sm:block' => $isStaff])>
                <x-skeleton.line size="sm" class="w-24" />
                <x-skeleton.line size="xl" class="mt-2 w-64 max-w-[75vw]" />
            </div>

            <div class="flex w-full flex-col gap-3 sm:max-w-2xl sm:flex-row sm:items-end">
                <div class="w-full space-y-2 sm:flex-1">
                    <x-skeleton.line size="sm" class="w-36" />
                    <x-skeleton.block class="h-11 rounded-xl" />
                </div>
                @if ($isStaff)
                    <div class="grid grid-cols-2 gap-3 sm:flex sm:shrink-0">
                        <x-skeleton.block class="h-11 rounded-xl sm:w-36" />
                        <x-skeleton.block class="h-11 rounded-xl sm:w-36" />
                    </div>
                @endif
            </div>
        </div>

        @if ($isStaff)
            <div class="grid grid-cols-2 gap-3 md:hidden">
                @foreach (range(1, 6) as $card)
                    <x-categories.library-mobile-card-skeleton />
                @endforeach
            </div>
        @endif

        <div @class([
            'grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3',
            'hidden md:grid' => $isStaff,
        ])>
            @foreach (range(1, 6) as $card)
                <x-categories.library-card-skeleton />
            @endforeach
        </div>
    </section>

    <div x-cloak x-show="skeletonFailed" role="alert"
        class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-6 text-sm text-amber-950">
        <p class="font-semibold">Não foi possível carregar as categorias.</p>
        <button type="button" x-on:click="window.location.reload()"
            class="mt-3 font-semibold text-blue-800 underline">Tentar novamente</button>
    </div>

    <span x-show="!skeletonFailed" class="sr-only">Carregando…</span>
</div>
