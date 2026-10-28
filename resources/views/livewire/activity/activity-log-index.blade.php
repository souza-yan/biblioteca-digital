<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        {{-- Cabeçalho da auditoria acessível somente ao administrador. --}}
        <header>
            <x-breadcrumb :items="[
                ['label' => 'Início', 'url' => route('dashboard')],
                ['label' => 'Atividades'],
            ]" />
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">
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

        {{-- A chave por aba força o Livewire a recriar tudo ao trocar de aba --}}
        <section role="tabpanel" aria-label="{{ $activeTabLabel }}" class="space-y-4"
            wire:key="panel-{{ $activeTab }}">

            <div wire:key="filters-{{ $activeTab }}">
                <x-activity.filters :fields="$filterFields" :option-groups="$filterOptions"
                    :clear-method="$clearFiltersMethod" :grid-columns="$filterGridColumns"
                    :user-suggestions="$userSuggestions" :active-tab="$activeTab"
                    :selected-user-filter="$selectedUserFilter" :user-search-value="$userSearchValue" />
            </div>

            <div wire:key="table-{{ $activeTab }}">
                <x-data-table :columns="$columns" :header-style="$tableHeaderStyle" :compact-header="true"
                    :sticky-header="true"
                    class="max-h-[70vh] overflow-auto [&_td]:text-lg [&_td]:text-black">
                    <x-data-table.loading :colspan="count($columns)" />
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
        </section>
    </div>
</div>
