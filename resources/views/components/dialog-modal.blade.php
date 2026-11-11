@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" :labelledBy="($id ?? md5($attributes->wire('model'))).'-title'" {{ $attributes }}>
    <div class="px-6 py-5">
        <h2 id="{{ ($id ?? md5($attributes->wire('model'))).'-title' }}" class="text-lg font-bold text-slate-900">
            {{ $title }}
        </h2>

        <div class="mt-3 text-sm leading-6 text-slate-600">
            {{ $content }}
        </div>
    </div>

    <div class="flex flex-wrap justify-end gap-3 bg-white px-6 py-4 text-end">
        {{ $footer }}
    </div>
</x-modal>
