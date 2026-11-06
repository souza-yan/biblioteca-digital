<div data-skeleton-layout="favorite-grid"
    class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
    <header class="mb-8 space-y-2">
        <div class="app-skeleton-block h-10 w-64 max-w-[75%] rounded-xl"></div>
        <div class="app-skeleton-block h-6 w-96 max-w-full rounded-full"></div>
    </header>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @foreach (range(1, 8) as $card)
            <article class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="app-skeleton-block aspect-[2480/3508] rounded-xl"></div>
                <div class="space-y-2 pt-3">
                    <div class="app-skeleton-block h-4 w-4/5 rounded-full"></div>
                    <div class="app-skeleton-block h-3 w-2/5 rounded-full"></div>
                </div>
                <div class="mt-3 flex justify-end gap-2 border-t border-slate-100 pt-3">
                    <div class="app-skeleton-block size-9 rounded-lg"></div>
                    <div class="app-skeleton-block size-9 rounded-lg"></div>
                </div>
            </article>
        @endforeach
    </div>
</div>
