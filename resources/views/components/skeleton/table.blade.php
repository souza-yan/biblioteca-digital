@props(['rows' => 6])

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="app-skeleton-block h-12 rounded-none"></div>
    <div class="divide-y divide-slate-100">
        @foreach (range(1, $rows) as $row)
            <div class="flex min-h-20 items-center gap-4 p-4">
                <div class="app-skeleton-block size-10 shrink-0 rounded-xl"></div>
                <div class="flex-1 space-y-2">
                    <div class="app-skeleton-block h-4 w-1/3 rounded-full"></div>
                    <div class="app-skeleton-block h-3 w-2/3 rounded-full"></div>
                </div>
                <div class="flex gap-2">
                    <div class="app-skeleton-block size-10 rounded-lg"></div>
                    <div class="app-skeleton-block size-10 rounded-lg"></div>
                </div>
            </div>
        @endforeach
    </div>
</section>
