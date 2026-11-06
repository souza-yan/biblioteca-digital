@props(['mode' => 'fallback'])

<div data-skeleton-layout="{{ $mode }}"
    class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
    @if ($mode === 'school-management')
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="app-skeleton-block h-10 w-44 rounded-xl"></div>
            <div class="app-skeleton-block h-11 w-full rounded-xl sm:w-36"></div>
        </header>
        <div class="max-w-xl space-y-2">
            <div class="app-skeleton-block h-4 w-28 rounded-full"></div>
            <div class="app-skeleton-block h-11 rounded-xl"></div>
        </div>
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            @foreach (range(1, 4) as $card)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 space-y-3">
                            <div class="app-skeleton-block h-6 w-3/4 rounded-full"></div>
                            <div class="app-skeleton-block h-4 w-1/2 rounded-full"></div>
                            <div class="app-skeleton-block h-4 w-5/6 rounded-full"></div>
                        </div>
                        <div class="flex gap-2">
                            <div class="app-skeleton-block size-12 rounded-lg"></div>
                            <div class="app-skeleton-block size-12 rounded-lg"></div>
                        </div>
                    </div>
                    <div class="mt-5 space-y-4 border-t border-slate-100 pt-5">
                        <div class="space-y-2">
                            <div class="app-skeleton-block h-4 w-32 rounded-full"></div>
                            <div class="app-skeleton-block h-3 w-24 rounded-full"></div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                            <div class="app-skeleton-block size-10 rounded-full"></div>
                            <div class="flex-1 space-y-2">
                                <div class="app-skeleton-block h-4 w-2/5 rounded-full"></div>
                                <div class="app-skeleton-block h-3 w-3/5 rounded-full"></div>
                            </div>
                            <div class="app-skeleton-block size-12 rounded-lg"></div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @elseif ($mode === 'most-downloaded')
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="space-y-2">
                <div class="app-skeleton-block h-4 w-20 rounded-full"></div>
                <div class="app-skeleton-block h-10 w-96 max-w-[75vw] rounded-xl"></div>
                <div class="app-skeleton-block h-5 w-[34rem] max-w-[85vw] rounded-full"></div>
            </div>
            <div class="app-skeleton-block size-12 rounded-lg"></div>
        </header>
        <section class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex items-end gap-3">
                    <div class="w-52 space-y-2">
                        <div class="app-skeleton-block h-4 w-32 rounded-full"></div>
                        <div class="app-skeleton-block h-11 rounded-xl"></div>
                    </div>
                    <div class="app-skeleton-block size-12 rounded-lg"></div>
                </div>
                <div class="app-skeleton-block h-4 w-24 rounded-full"></div>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach (range(1, 6) as $row)
                    <div class="flex items-center gap-4 py-4">
                        <div class="app-skeleton-block size-10 rounded-full"></div>
                        <div class="flex-1 space-y-2">
                            <div class="app-skeleton-block h-4 w-1/3 rounded-full"></div>
                            <div class="app-skeleton-block h-3 w-1/5 rounded-full"></div>
                        </div>
                        <div class="space-y-2">
                            <div class="app-skeleton-block ml-auto h-4 w-10 rounded-full"></div>
                            <div class="app-skeleton-block h-3 w-20 rounded-full"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @elseif ($mode === 'activity-management')
        <header><div class="app-skeleton-block h-10 w-48 rounded-xl"></div></header>
        <nav class="flex gap-2 border-b border-slate-200 pb-px">
            @foreach (range(1, 3) as $tab)
                <div class="app-skeleton-block h-12 w-32 rounded-t-lg"></div>
            @endforeach
        </nav>
        <section class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach (range(1, 4) as $filter)
                    <div class="space-y-2">
                        <div class="app-skeleton-block h-4 w-24 rounded-full"></div>
                        <div class="app-skeleton-block h-11 rounded-xl"></div>
                    </div>
                @endforeach
            </div>
            <x-skeleton.table :rows="6" />
        </section>
    @elseif ($mode === 'user-management')
        <div class="mb-8"><div class="app-skeleton-block h-10 w-72 max-w-[80%] rounded-xl"></div></div>
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid w-full max-w-4xl flex-1 gap-4 md:grid-cols-2">
                @foreach (range(1, 2) as $filter)
                    <div class="space-y-2">
                        <div class="app-skeleton-block h-4 w-44 rounded-full"></div>
                        <div class="app-skeleton-block h-11 rounded-xl"></div>
                    </div>
                @endforeach
            </div>
            <div class="app-skeleton-block h-11 w-full rounded-xl lg:w-40"></div>
        </div>
        <div class="space-y-3 md:hidden">
            @foreach (range(1, 5) as $card)
                <article class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="app-skeleton-block size-12 rounded-full"></div>
                    <div class="flex-1 space-y-2">
                        <div class="app-skeleton-block h-4 w-1/2 rounded-full"></div>
                        <div class="app-skeleton-block h-3 w-4/5 rounded-full"></div>
                    </div>
                    <div class="flex gap-2">
                        <div class="app-skeleton-block size-10 rounded-lg"></div>
                        <div class="app-skeleton-block size-10 rounded-lg"></div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="hidden md:block"><x-skeleton.table :rows="6" /></div>
    @elseif ($mode === 'category-management')
        <div class="mb-8"><div class="app-skeleton-block h-10 w-52 rounded-xl"></div></div>
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="w-full max-w-xl space-y-2">
                <div class="app-skeleton-block h-4 w-32 rounded-full"></div>
                <div class="app-skeleton-block h-11 rounded-xl"></div>
            </div>
            <div class="app-skeleton-block h-11 w-full rounded-xl sm:w-40"></div>
        </div>
        <x-skeleton.table :rows="6" />
    @elseif ($mode === 'material-management')
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div class="app-skeleton-block h-10 w-52 rounded-xl"></div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <div class="app-skeleton-block h-11 w-full rounded-xl sm:w-44"></div>
                <div class="app-skeleton-block h-11 w-full rounded-xl sm:w-40"></div>
            </div>
        </div>
        <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach (range(1, 3) as $filter)
                <div class="space-y-2">
                    <div class="app-skeleton-block h-4 w-28 rounded-full"></div>
                    <div class="app-skeleton-block h-11 rounded-xl"></div>
                </div>
            @endforeach
        </div>
        <x-skeleton.table :rows="6" />
    @else
        <div class="flex items-center justify-between gap-4">
            <div class="app-skeleton-block h-10 w-64 rounded-xl"></div>
            <div class="app-skeleton-block h-11 w-36 rounded-xl"></div>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach (range(1, 2) as $filter)
                <div class="space-y-2">
                    <div class="app-skeleton-block h-4 w-28 rounded-full"></div>
                    <div class="app-skeleton-block h-11 rounded-xl"></div>
                </div>
            @endforeach
        </div>
        <x-skeleton.table :rows="6" />
    @endif
</div>
