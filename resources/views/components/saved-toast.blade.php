@props([
    'on' => 'saved',
    'title' => 'Alterações salvas!',
    'message' => 'Alterações salvas com sucesso!',
])

<x-action-message :on="$on"
    class="fixed right-4 top-20 z-50 w-[calc(100%-2rem)] max-w-sm rounded-xl border border-emerald-200 bg-white p-4 text-left shadow-lg sm:right-6 sm:w-full">
    <span class="flex items-start gap-3">
        <x-ts-icon icon="check-circle" solid class="mt-0.5 size-6 shrink-0 text-emerald-600" />
        <span class="min-w-0">
            <strong class="block font-semibold text-slate-900">{{ $title }}</strong>
            <span class="mt-0.5 block text-sm font-normal text-slate-600">{{ $message }}</span>
        </span>
    </span>
</x-action-message>
