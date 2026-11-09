@props(['columns', 'headerStyle' => 'blue', 'compactHeader' => false])

<div {{ $attributes->class('overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm') }}>
    <table class="min-w-full divide-y divide-slate-200">
        <thead @class(['bg-blue-800' => $headerStyle === 'blue', 'bg-slate-50' => $headerStyle === 'gray'])>
            <tr>
                @foreach ($columns as $column)
                    <th @class(['py-3 text-base font-semibold uppercase', 'px-4' => $compactHeader, 'px-5' => ! $compactHeader, 'text-right' => ($column['align'] ?? 'left') === 'right', 'text-left' => ($column['align'] ?? 'left') !== 'right', 'whitespace-nowrap' => $column['nowrap'] ?? false])>
                        <x-skeleton.text class="w-fit">{{ $column['label'] }} @isset($column['sortAction']) ↑ @endisset</x-skeleton.text>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">{{ $slot }}</tbody>
    </table>
</div>
