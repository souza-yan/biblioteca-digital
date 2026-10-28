@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'h-11 rounded-lg border-slate-300 bg-white text-base text-slate-900 shadow-sm focus:border-blue-600 focus:ring-blue-600 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500']) !!}>
