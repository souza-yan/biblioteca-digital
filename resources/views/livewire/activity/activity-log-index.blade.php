<div>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Administração</p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">Registro de atividades</h1>
        </div>

        <div class="mb-5 rounded-md border border-gray-200 bg-white p-5 shadow-sm">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <label class="block text-sm font-medium text-gray-700">
                    Usuário
                    <select wire:model.live="userFilter" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        <option value="">Todos os usuários</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-sm font-medium text-gray-700">
                    Ação
                    <select wire:model.live="actionFilter" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        <option value="">Todas as ações</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action->value }}">{{ $action->label() }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-sm font-medium text-gray-700">
                    Material
                    <select wire:model.live="materialFilter" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        <option value="">Todos os materiais</option>
                        @foreach ($materials as $material)
                            <option value="{{ $material->id }}">{{ $material->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-sm font-medium text-gray-700">
                    De
                    <input wire:model.live="fromDate" type="date" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                </label>

                <label class="block text-sm font-medium text-gray-700">
                    Até
                    <input wire:model.live="untilDate" type="date" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                </label>
            </div>

            <div class="mt-4 flex justify-end">
                <x-ts-button color="slate" wire:click="clearFilters">Limpar filtros</x-ts-button>
            </div>

            @foreach (['userFilter', 'actionFilter', 'materialFilter', 'fromDate', 'untilDate'] as $filter)
                @error($filter)
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            @endforeach
        </div>

        <div class="overflow-x-auto rounded-md border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Data</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Usuário</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Ação</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Descrição</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Material</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr wire:key="activity-log-{{ $log->id }}">
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-5 py-4 text-sm text-gray-900">{{ $log->user->name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-900">{{ $log->action->label() }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $log->description }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $log->material?->title ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">Nenhuma atividade encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</div>
