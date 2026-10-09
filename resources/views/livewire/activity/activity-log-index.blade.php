<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <header>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">
                Atividades
            </h1>
        </header>

        <nav class="flex flex-wrap gap-2 border-b border-blue-200" role="tablist" aria-label="Tipos de atividade">
            @foreach ($tabLabels as $tab => $label)
                <button type="button" role="tab" aria-selected="{{ $activeTab === $tab ? 'true' : 'false' }}"
                    wire:click="selectTab('{{ $tab }}')"
                    class="{{ $activeTab === $tab
                        ? 'border-b-2 border-blue-800 bg-blue-800 text-white'
                        : 'border-b-2 border-transparent text-blue-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800' }} -mb-px rounded-t-lg px-4 py-3 text-lg font-medium transition-colors duration-200">
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        <section role="tabpanel" aria-label="{{ $activeTabLabel }}" class="space-y-4">
            <x-activity.filters :fields="$filterFields" :option-groups="$filterOptions" :clear-method="$clearFiltersMethod" :grid-columns="$filterGridColumns" />

            <x-data-table :columns="$columns" :header-style="$tableHeaderStyle" :compact-header="true" :sticky-header="true"
                class="max-h-[70vh] overflow-auto [&_td]:text-lg [&_td]:text-black">
                @forelse ($records as $record)
                    @if ($activeTab === 'downloads')
                        <x-activity.download-row :download="$record" />
                    @else
                        <x-activity.log-row :log="$record" :row-type="$activeTab === 'changes' ? 'change' : 'access'" :badge-classes="$actionBadgeClasses" />
                    @endif
                @empty
                    <x-data-table.empty-state :colspan="count($columns)" :compact="true">
                        <span class="text-lg">{{ $emptyMessage }}</span>
                    </x-data-table.empty-state>
                @endforelse
            </x-data-table>

            <x-pagination :paginator="$records" />
        </section>
    </div>
</div>
