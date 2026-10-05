<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Painel</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if ($isTeacher)
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">Materiais publicados recentemente</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @forelse ($publishedMaterials as $material)
                            <a class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm" href="{{ route('painel.library.show', $material) }}">
                                <p class="font-semibold text-gray-900">{{ $material->title }}</p>
                                <p class="mt-2 text-sm text-gray-600">{{ $material->category->name }}</p>
                            </a>
                        @empty
                            <p class="text-sm text-gray-600">Ainda não há materiais publicados.</p>
                        @endforelse
                    </div>
                </section>

                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">Meus favoritos</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @forelse ($favoriteMaterials as $material)
                            <a class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm" href="{{ route('painel.library.show', $material) }}">
                                <p class="font-semibold text-gray-900">{{ $material->title }}</p>
                                <p class="mt-2 text-sm text-gray-600">{{ $material->category->name }}</p>
                            </a>
                        @empty
                            <p class="text-sm text-gray-600">Você ainda não favoritou materiais publicados.</p>
                        @endforelse
                    </div>
                </section>
            @else
                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-sm text-gray-600">Usuários ativos</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $activeUsersCount }}</p>
                    </div>
                    @foreach ($statusCounts as $status => $total)
                        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="text-sm text-gray-600">Materiais: {{ $status }}</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $total }}</p>
                        </div>
                    @endforeach
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-sm text-gray-600">Downloads nos últimos 30 dias</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $recentDownloadsCount }}</p>
                    </div>
                </section>

                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">Atividades recentes</h3>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Atividade</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Usuário</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Material</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Data</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($recentActivities as $activity)
                                    <tr>
                                        <td class="px-5 py-4 text-sm text-gray-900">{{ $activity->description }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600">{{ $activity->user->name }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600">{{ $activity->material?->title ?? '—' }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600">{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">Nenhuma atividade registrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
