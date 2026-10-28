@props(['columns', 'headerStyle' => 'blue', 'stickyHeader' => false, 'compactHeader' => false])

<div {{ $attributes->class('overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm') }}>
    <table class="min-w-full divide-y divide-slate-200">
        <thead @class([
            'z-10' => $stickyHeader,
            'sticky top-0' => $stickyHeader,
            'bg-blue-800' => $headerStyle === 'blue',
            'bg-slate-50' => $headerStyle === 'gray',
        ])>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" @class([
                        'px-4 py-3 text-base font-semibold uppercase' => $compactHeader,
                        'px-5 py-3 text-base font-semibold uppercase' => !$compactHeader,
                        'text-left' => ($column['align'] ?? 'left') === 'left',
                        'text-right' => ($column['align'] ?? 'left') === 'right',
                        'whitespace-nowrap' => $column['nowrap'] ?? false,
                        'text-white' => $headerStyle === 'blue',
                        'text-slate-600' => $headerStyle === 'gray',
                    ])>
                        {{ $column['label'] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            {{ $slot }}
        </tbody>
    </table>
</div>
