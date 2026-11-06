@php
    $user = auth()->user();
    $isTeacher = $user?->isTeacher() ?? false;
    $isStaff = $user?->isStaff() ?? false;
@endphp

<div data-skeleton-layout="dashboard" class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
    @if ($isTeacher)
        <section class="space-y-3 rounded-3xl bg-blue-800 px-5 py-7 sm:px-8 sm:py-9">
            <div class="app-skeleton-block h-9 w-[34rem] max-w-[80%] rounded-xl"></div>
            <div class="app-skeleton-block h-5 w-[31rem] max-w-[90%] rounded-full"></div>
            <div class="app-skeleton-block mt-6 h-12 w-full rounded-xl"></div>
        </section>
        <section class="space-y-5">
            <div class="app-skeleton-block h-7 w-56 rounded-lg"></div>
            <div class="grid grid-cols-[2.5rem_minmax(0,1fr)_2.5rem] items-center gap-3">
                <div class="app-skeleton-block size-10 rounded-full"></div>
                <div class="grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach (range(1, 3) as $card)
                        <div class="flex h-20 items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="app-skeleton-block size-11 rounded-xl"></div><div class="flex-1 space-y-2"><div class="app-skeleton-block h-4 w-1/2 rounded-full"></div><div class="app-skeleton-block h-3 w-2/3 rounded-full"></div></div></div>
                    @endforeach
                </div>
                <div class="app-skeleton-block size-10 rounded-full"></div>
            </div>
        </section>
        <div class="grid min-w-0 grid-cols-1 gap-8 xl:grid-cols-[minmax(0,3fr)_minmax(19rem,2fr)]">
            <section class="space-y-5"><div class="app-skeleton-block h-7 w-80 max-w-[85%] rounded-lg"></div><div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">@foreach (range(1, 3) as $card)<article class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm"><div class="app-skeleton-block aspect-[2480/3508] rounded-xl"></div><div class="space-y-2 p-1 pt-4"><div class="app-skeleton-block h-3 w-1/3 rounded-full"></div><div class="app-skeleton-block h-5 w-4/5 rounded-full"></div><div class="app-skeleton-block h-3 w-full rounded-full"></div></div></article>@endforeach</div></section>
            <section class="space-y-5"><div class="app-skeleton-block h-7 w-64 rounded-lg"></div><div class="space-y-3">@foreach (range(1, 3) as $row)<div class="flex h-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="app-skeleton-block h-full w-20 rounded-none"></div><div class="flex flex-1 flex-col justify-center gap-3 p-4"><div class="app-skeleton-block h-4 w-3/4 rounded-full"></div><div class="app-skeleton-block h-3 w-1/3 rounded-full"></div></div></div>@endforeach</div></section>
        </div>
    @elseif ($isStaff)
        <div class="app-skeleton-block h-28 rounded-2xl"></div>
        <div class="grid grid-cols-3 gap-2 sm:gap-4">@foreach (range(1, 3) as $card)<div class="h-28 rounded-2xl border border-slate-200 bg-white p-4"><div class="app-skeleton-block h-4 w-3/4 rounded-full"></div><div class="app-skeleton-block mt-3 h-8 w-1/3 rounded-lg"></div></div>@endforeach</div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6"><div class="app-skeleton-block h-6 w-36 rounded-full"></div><div class="mt-4 grid grid-cols-3 gap-2">@foreach (range(1, 3) as $card)<div class="app-skeleton-block h-28 rounded-xl"></div>@endforeach</div></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6"><div class="app-skeleton-block h-6 w-56 rounded-full"></div><div class="mt-4 space-y-3">@foreach (range(1, 3) as $row)<div class="app-skeleton-block h-16 rounded-xl"></div>@endforeach</div></div>
    @else
        <div class="flex items-center justify-between"><div class="app-skeleton-block h-10 w-56 rounded-xl"></div><div class="app-skeleton-block h-5 w-28 rounded-full"></div></div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">@foreach (range(1, 4) as $card)<div class="flex h-36 items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5"><div class="app-skeleton-block size-14 rounded-2xl"></div><div class="flex-1 space-y-3"><div class="app-skeleton-block h-5 w-3/4 rounded-full"></div><div class="app-skeleton-block h-10 w-1/3 rounded-lg"></div><div class="app-skeleton-block h-4 w-full rounded-full"></div></div></div>@endforeach</div>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">@foreach (range(1, 2) as $section)<div class="h-80 rounded-2xl border border-slate-200 bg-white p-6"><div class="app-skeleton-block h-7 w-52 rounded-full"></div><div class="mt-8 space-y-6">@foreach (range(1, 3) as $row)<div class="space-y-2"><div class="app-skeleton-block h-4 w-1/3 rounded-full"></div><div class="app-skeleton-block h-3 w-full rounded-full"></div></div>@endforeach</div></div>@endforeach</div>
    @endif
</div>
