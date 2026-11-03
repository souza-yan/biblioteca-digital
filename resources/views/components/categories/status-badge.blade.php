@props(['active'])

<span @class([
    'inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold',
    'bg-emerald-100 text-emerald-800' => $active,
    'bg-slate-100 text-slate-700' => ! $active,
])>
    {{ $active ? 'Ativa' : 'Inativa' }}
</span>
