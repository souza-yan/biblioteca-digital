<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        {{-- Cabeçalho da auditoria acessível somente ao administrador. --}}
        <x-skeleton.gate name="activity-controls" label="Carregando controles de atividades">
            <x-slot:skeleton>
                <div class="space-y-6 lg:space-y-8">
                    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl"><x-skeleton.text class="w-fit">Atividades</x-skeleton.text></h1>
                    <div class="flex flex-wrap gap-2 border-b border-blue-200">
                        @foreach ($tabLabels as $label)
                            <div class="-mb-px rounded-t-lg px-4 py-3 text-lg font-medium"><x-skeleton.text>{{ $label }}</x-skeleton.text></div>
                        @endforeach
                    </div>
                </div>
            </x-slot:skeleton>
            <div class="space-y-6 lg:space-y-8">
        <header>
            <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">
                Atividades
            </h1>
        </header>

        {{-- Abas separam downloads, alterações administrativas e acessos. --}}
        <nav class="flex flex-wrap gap-2 border-b border-blue-200" role="tablist" aria-label="Tipos de atividade">
            @foreach ($tabLabels as $tab => $label)
                <button type="button" role="tab" wire:key="tab-button-{{ $tab }}"
                    aria-selected="{{ $activeTab === $tab ? 'true' : 'false' }}"
                    wire:click="selectTab('{{ $tab }}')"
                    class="{{ $activeTab === $tab
                        ? 'border-b-2 border-blue-800 bg-blue-800 text-white'
                        : 'border-b-2 border-transparent text-blue-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800' }} -mb-px rounded-t-lg px-4 py-3 text-lg font-medium transition-colors duration-200">
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        {{-- Filtros e tabela mantêm chaves por aba; o gate permanece estável. --}}
            </div>
        </x-skeleton.gate>
        <section role="tabpanel" aria-label="{{ $activeTabLabel }}" class="space-y-4"
            wire:key="activity-panel">

            <x-skeleton.gate name="activity-filters" label="Carregando filtros de atividades">
                <x-slot:skeleton>
                    <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="grid grid-cols-1 gap-4 {{ $filterGridColumns }}">
                            @foreach ($filterFields as $field)
                                <div class="text-base font-medium"><x-skeleton.text class="w-fit">{{ $field['label'] }}</x-skeleton.text><x-skeleton.block class="mt-1 h-11 rounded-lg" /></div>
                            @endforeach
                        </div>
                        <div class="mt-4 flex justify-end"><x-skeleton.block class="size-12 rounded-lg" /></div>
                    </div>
                </x-slot:skeleton>
            <div wire:key="filters-{{ $activeTab }}">
                <x-activity.filters :fields="$filterFields" :option-groups="$filterOptions"
                    :clear-method="$clearFiltersMethod" :grid-columns="$filterGridColumns"
                    :user-suggestions="$userSuggestions" :active-tab="$activeTab"
                    :selected-user-filter="$selectedUserFilter" :user-search-value="$userSearchValue" />
            </div>

            </x-skeleton.gate>

            <x-skeleton.gate name="activity-list" label="Carregando listagem de atividades" :pagination-page="$records->getPageName()">
                <x-slot:skeleton>
                    @include('livewire.activity.activity-list-skeleton')
                </x-slot:skeleton>
            <div class="space-y-4">
            <div wire:key="table-{{ $activeTab }}">
                <x-data-table :columns="$columns" :header-style="$tableHeaderStyle" :compact-header="true"
                    :sticky-header="true"
                    class="max-h-[70vh] overflow-auto [&_td]:text-lg [&_td]:text-black">
                    @forelse ($records as $record)
                        @if ($activeTab === 'downloads')
                            <x-activity.download-row wire:key="download-{{ $record->id }}" :download="$record" />
                        @else
                            <x-activity.log-row wire:key="{{ $activeTab }}-{{ $record->id }}" :log="$record"
                                :row-type="$activeTab === 'changes' ? 'change' : 'access'"
                                :badge-classes="$actionBadgeClasses" />
                        @endif
                    @empty
                        <x-data-table.empty-state :colspan="count($columns)" :compact="true">
                            <span class="text-lg">{{ $emptyMessage }}</span>
                        </x-data-table.empty-state>
                    @endforelse
                </x-data-table>
            </div>

            <x-pagination :paginator="$records" />
            </div>
            </x-skeleton.gate>
        </section>
    </div>
</div>
