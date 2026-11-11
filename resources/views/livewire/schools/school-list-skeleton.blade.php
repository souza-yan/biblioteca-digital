<div class="space-y-6 lg:space-y-8">
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        @forelse ($schools as $school)
            <article class="flex min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <x-skeleton.text class="break-words text-xl font-bold">{{ $school->name }}</x-skeleton.text>
                        <x-skeleton.text class="mt-2 text-sm">{{ $school->type->label() }} · Região {{ $school->region->label() }}</x-skeleton.text>
                        <x-skeleton.text class="mt-1 break-words text-sm">{{ $school->address }}</x-skeleton.text>
                        @if ($school->phone)
                            <x-skeleton.text class="mt-1 text-sm">{{ $school->phone }}</x-skeleton.text>
                        @endif
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <x-skeleton.block class="size-12 rounded-lg" />
                        <x-skeleton.block class="size-12 rounded-lg" />
                    </div>
                </div>
                <div class="mt-5 flex-1 border-t border-slate-100 pt-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <x-skeleton.text class="font-semibold">Gestão vinculada</x-skeleton.text>
                            <x-skeleton.text class="text-sm">{{ $school->users->count() }} {{ $school->users->count() === 1 ? 'gestor vinculado' : 'gestores vinculados' }}</x-skeleton.text>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2">
                        @forelse ($school->users as $manager)
                            <div class="flex min-w-0 flex-wrap items-center gap-3 rounded-xl bg-slate-50 px-3 py-2.5">
                                <x-skeleton.circle />
                                <div class="min-w-0 flex-1">
                                    <x-skeleton.text class="truncate text-sm font-semibold">{{ $manager->name }}</x-skeleton.text>
                                    <x-skeleton.text class="truncate text-xs">{{ $manager->email }}</x-skeleton.text>
                                    <x-skeleton.text class="mt-1 w-fit rounded-full px-2 py-0.5 text-xs font-semibold">{{ $manager->is_active ? 'Ativo' : 'Inativo' }}</x-skeleton.text>
                                </div>
                                <div class="flex w-full shrink-0 justify-end gap-2 sm:w-auto">
                                    <x-skeleton.block class="size-12 shrink-0 rounded-lg" />
                                    <x-skeleton.block class="size-12 shrink-0 rounded-lg" />
                                    <x-skeleton.block class="size-12 shrink-0 rounded-lg" />
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 px-4 py-3">
                                <x-skeleton.text class="text-sm">Nenhum gestor ativo vinculado a esta escola.</x-skeleton.text>
                            </div>
                        @endforelse
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center xl:col-span-2">
                <x-skeleton.block class="mx-auto size-12 rounded-lg" />
                <x-skeleton.text class="mt-3 text-base font-semibold">Nenhuma escola encontrada.</x-skeleton.text>
                <x-skeleton.text class="mt-1 text-sm">Cadastre uma escola ou ajuste o termo pesquisado.</x-skeleton.text>
            </div>
        @endforelse
    </div>
    <x-skeleton.pagination :paginator="$schools" />
</div>
