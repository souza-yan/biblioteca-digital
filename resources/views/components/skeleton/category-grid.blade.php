@php($isStaff = auth()->user()?->isStaff() ?? false)

<div data-skeleton-layout="category-library"
    class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
    <section @class([
        'overflow-hidden rounded-2xl bg-blue-800 text-white shadow-sm sm:px-9 sm:py-10 lg:px-12 lg:py-12',
        'px-5 py-6' => $isStaff,
        'px-6 py-8' => ! $isStaff,
    ])>
        <div class="app-skeleton-block h-6 w-44 rounded-full"></div>
        <div class="app-skeleton-block mt-3 h-12 w-56 max-w-[70%] rounded-xl"></div>
        <div class="app-skeleton-block mt-4 h-7 w-[42rem] max-w-[90%] rounded-full"></div>
    </section>

    <section class="space-y-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div @class(['hidden sm:block' => $isStaff])>
                <div class="app-skeleton-block h-5 w-24 rounded-full"></div>
                <div class="app-skeleton-block mt-2 h-8 w-64 max-w-[75vw] rounded-lg"></div>
            </div>

            <div class="flex w-full flex-col gap-3 sm:max-w-2xl sm:flex-row sm:items-end">
                <div class="w-full sm:flex-1">
                    <div class="app-skeleton-block h-4 w-36 rounded-full"></div>
                    <div class="app-skeleton-block mt-2 h-11 w-full rounded-xl"></div>
                </div>
                @if ($isStaff)
                    <div class="grid grid-cols-2 gap-3 sm:flex sm:shrink-0">
                        <div class="app-skeleton-block h-11 w-full rounded-xl sm:w-36"></div>
                        <div class="app-skeleton-block h-11 w-full rounded-xl sm:w-36"></div>
                    </div>
                @endif
            </div>
        </div>

        @if ($isStaff)
            <div class="grid grid-cols-2 gap-3 md:hidden">
                @foreach (range(1, 6) as $card)
                    <article class="flex min-h-40 flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="app-skeleton-block size-10 rounded-xl"></div>
                        <div class="app-skeleton-block mt-3 h-4 w-4/5 rounded-full"></div>
                        <div class="app-skeleton-block mt-auto h-3 w-20 rounded-full"></div>
                        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                            <div class="app-skeleton-block h-6 w-16 rounded-full"></div>
                            <div class="flex gap-2">
                                <div class="app-skeleton-block size-9 rounded-lg"></div>
                                <div class="app-skeleton-block size-9 rounded-lg"></div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div @class([
            'grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3',
            'hidden md:grid' => $isStaff,
        ])>
            @foreach (range(1, 6) as $card)
                <article class="flex min-h-48 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="app-skeleton-block size-12 rounded-2xl"></div>
                    <div class="app-skeleton-block mt-4 h-6 w-2/3 rounded-full"></div>
                    <div class="app-skeleton-block mt-2 h-5 w-full rounded-full"></div>
                    <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
                        <div class="app-skeleton-block h-5 w-36 rounded-full"></div>
                        <div class="app-skeleton-block h-5 w-28 rounded-full"></div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>
