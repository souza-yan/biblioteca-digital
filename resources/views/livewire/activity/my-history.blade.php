<div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
    {{-- Cabeçalho da área pessoal de rastreabilidade do acervo. --}}
    <header>
        <x-breadcrumb :items="[
            ['label' => 'Início', 'url' => route('dashboard')],
            ['label' => 'Meu histórico'],
        ]" />
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Meu histórico</h1>
    </header>

    {{-- Abas preservadas na URL para facilitar o retorno à visão selecionada. --}}
    <nav class="flex gap-2 border-b border-slate-200" aria-label="Tipos de histórico" role="tablist">
        @foreach (['downloads' => 'Downloads', 'accesses' => 'Pré-visualizações'] as $tab => $label)
            <button type="button" wire:key="history-tab-{{ $tab }}" wire:click="$set('activeTab', '{{ $tab }}')"
                role="tab" aria-selected="{{ $activeTab === $tab ? 'true' : 'false' }}"
                @class([
                    'border-b-2 px-4 py-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-inset',
                    'border-blue-800 text-blue-800' => $activeTab === $tab,
                    'border-transparent text-slate-500 hover:text-slate-800' => $activeTab !== $tab,
                ])>
                {{ $label }}
            </button>
        @endforeach
    </nav>

    <div class="max-w-xl">
        <x-search-field model="search" label="Buscar no histórico" placeholder="Digite o título do material" />
    </div>

    {{-- Registros pessoais; a consulta sempre aplica o usuário autenticado. --}}
    <section role="tabpanel" aria-label="{{ $activeTab === 'downloads' ? 'Downloads' : 'Pré-visualizações' }}">
        <x-data-table :columns="[
            ['label' => 'Data e hora', 'nowrap' => true],
            ['label' => 'Material'],
            ['label' => 'Categoria'],
            ['label' => $activeTab === 'downloads' ? 'Versão' : 'Ação'],
        ]">
            <x-data-table.loading :colspan="4" />
            @forelse ($records as $record)
                @php
                    $material = $record->material;
                    $occurredAt = $activeTab === 'downloads' ? $record->downloaded_at : $record->created_at;
                @endphp
                <tr wire:key="history-{{ $activeTab }}-{{ $record->id }}">
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                        {{ $occurredAt->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-5 py-4 font-semibold text-slate-900">
                        @if ($material)
                            <a href="{{ route(auth()->user()->isAdmin() ? 'painel.materials.show' : 'painel.library.show', $material) }}"
                                class="text-blue-800 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-600">
                                {{ $material->title }}
                            </a>
                        @else
                            Material indisponível
                        @endif
                    </td>
                    <td class="px-5 py-4 text-slate-600">{{ $material?->category?->name ?? '—' }}</td>
                    <td class="px-5 py-4 text-slate-600">
                        {{ $activeTab === 'downloads' ? 'v'.($record->version?->version_number ?? '—') : 'Pré-visualização' }}
                    </td>
                </tr>
            @empty
                <x-data-table.empty-state :colspan="4">
                    Nenhum registro encontrado neste histórico.
                </x-data-table.empty-state>
            @endforelse
        </x-data-table>

        <x-pagination :paginator="$records" class="mt-4" />
    </section>
</div>
