@php($isTeacher = auth()->user()?->isTeacher() ?? false)

<div data-skeleton-layout="material-detail"
    class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
    <article class="rounded-md border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start sm:gap-4">
            <div class="space-y-2">
                <div class="app-skeleton-block h-9 w-80 max-w-[70vw] rounded-xl"></div>
                @if ($isTeacher)
                    <div class="app-skeleton-block h-4 w-40 rounded-full"></div>
                @endif
            </div>
            <div class="flex gap-2">
                @foreach (range(1, $isTeacher ? 2 : 1) as $action)
                    <div class="app-skeleton-block size-10 rounded-lg sm:size-12"></div>
                @endforeach
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 border-t border-slate-100 pt-4 sm:mt-6 sm:grid-cols-2 sm:gap-4 sm:pt-5">
            <div class="space-y-2">
                <div class="app-skeleton-block h-3 w-16 rounded-full"></div>
                <div class="app-skeleton-block h-6 w-24 rounded-full"></div>
            </div>
            <div class="hidden space-y-2 sm:block">
                <div class="app-skeleton-block h-3 w-24 rounded-full"></div>
                <div class="app-skeleton-block h-5 w-40 rounded-full"></div>
            </div>
            <div class="space-y-2 sm:col-span-2">
                <div class="app-skeleton-block h-3 w-20 rounded-full"></div>
                <div class="app-skeleton-block h-5 w-full rounded-full"></div>
                <div class="app-skeleton-block h-5 w-3/4 rounded-full"></div>
            </div>
        </div>

        @if ($isTeacher)
            <div class="app-skeleton-block mt-6 aspect-video w-full rounded-xl"></div>
        @endif
    </article>

    @unless ($isTeacher)
        <section class="flex flex-col rounded-md border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="app-skeleton-block h-6 w-24 rounded-full"></div>
            <div class="mt-4 hidden sm:block">
                <x-skeleton.table :rows="3" />
            </div>
            <div class="mt-4 space-y-3 sm:hidden">
                @foreach (range(1, 3) as $version)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="app-skeleton-block h-4 w-24 rounded-full"></div>
                        <div class="app-skeleton-block mt-2 h-3 w-3/4 rounded-full"></div>
                        <div class="mt-3 flex gap-2">
                            <div class="app-skeleton-block h-6 w-14 rounded-full"></div>
                            <div class="app-skeleton-block h-6 w-20 rounded-full"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-8 space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-6">
                <div class="app-skeleton-block h-14 rounded-xl"></div>
                <div class="app-skeleton-block h-11 rounded-xl"></div>
                <div class="flex justify-end"><div class="app-skeleton-block h-11 w-36 rounded-xl"></div></div>
            </div>
        </section>
    @endunless
</div>
