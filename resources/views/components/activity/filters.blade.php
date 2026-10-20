@props([
    'fields',
    'optionGroups',
    'clearMethod',
    'gridColumns' => 'md:grid-cols-2 xl:grid-cols-4',
    'userSuggestions' => collect(),
    'activeTab',
    'selectedUserFilter' => '',
    'userSearchValue' => '',
])

<div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
    <div {{ $attributes->class('grid grid-cols-1 gap-4 '.$gridColumns) }}>
        @foreach ($fields as $field)
            @php($fieldId = 'activity-'.$activeTab.'-'.$field['model'])
            <div class="relative block text-base font-medium text-slate-800">
                <label for="{{ $fieldId }}">{{ $field['label'] }}</label>

                @if ($field['type'] === 'user-search')
                    <div class="relative mt-1">
                        <input id="{{ $fieldId }}" type="search" wire:model.live.debounce.300ms="{{ $field['searchModel'] }}"
                            placeholder="{{ $field['placeholder'] }}" autocomplete="off"
                            class="block h-11 w-full rounded-lg border border-slate-300 bg-white px-3 pr-11 text-base text-slate-900 shadow-sm focus:border-blue-600 focus:ring-blue-600">

                        @if ($selectedUserFilter !== '')
                            <button type="button" wire:click="clearFilterUser('{{ $activeTab }}')"
                                class="absolute right-1 top-1/2 inline-flex size-9 -translate-y-1/2 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100"
                                aria-label="Limpar usuário selecionado" title="Limpar usuário">
                                <x-ts-icon icon="x-mark" outline class="size-5" />
                            </button>
                        @endif
                    </div>

                    @if ($selectedUserFilter === '' && filled($userSearchValue))
                        <ul class="absolute z-30 mt-1 w-full overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                            role="listbox">
                            @forelse ($userSuggestions as $user)
                                <li wire:key="activity-user-{{ $activeTab }}-{{ $user->id }}">
                                    <button type="button" wire:click="selectFilterUser('{{ $activeTab }}', {{ $user->id }})"
                                        class="flex w-full flex-col px-3 py-2 text-left hover:bg-blue-50 focus:bg-blue-50 focus:outline-none"
                                        role="option">
                                        <span class="font-medium text-slate-900">{{ $user->name }}</span>
                                        <span class="text-sm text-slate-500">{{ $user->email }}</span>
                                    </button>
                                </li>
                            @empty
                                <li class="px-3 py-2 text-sm text-slate-500">Nenhum usuário encontrado.</li>
                            @endforelse
                        </ul>
                    @endif
                @elseif ($field['type'] === 'select')
                    <select
                        id="{{ $fieldId }}"
                        wire:model.live="{{ $field['model'] }}"
                        class="mt-1 block h-11 w-full rounded-lg border-slate-300 text-base text-slate-900 shadow-sm focus:border-blue-600 focus:ring-blue-600"
                    >
                        <option value="">{{ $field['placeholder'] }}</option>
                        @foreach ($optionGroups[$field['options']] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @else
                    <input
                        id="{{ $fieldId }}"
                        wire:model.live="{{ $field['model'] }}"
                        type="date"
                        class="mt-1 block h-11 w-full rounded-lg border-slate-300 text-base text-slate-900 shadow-sm focus:border-blue-600 focus:ring-blue-600"
                    >
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-4 flex justify-end">
        <x-ts-button color="blue" class="!size-12 !p-0 justify-center text-white" wire:click="{{ $clearMethod }}"
            aria-label="Limpar filtros" title="Limpar filtros">
            <x-ts-icon icon="arrow-path" outline class="size-7" />
        </x-ts-button>
    </div>

    @foreach ($fields as $field)
        @error($field['model'])
            <p class="mt-2 text-base text-red-600">{{ $message }}</p>
        @enderror
    @endforeach
</div>
