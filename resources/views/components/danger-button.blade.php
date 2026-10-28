<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-transparent bg-red-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>
