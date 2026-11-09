<div class="space-y-6 lg:space-y-8">
    <div class="space-y-3 md:hidden">
        @forelse ($users as $user)
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <x-skeleton.block class="size-12 shrink-0 rounded-full" />
                        <div class="min-w-0">
                            <x-skeleton.text class="truncate text-base font-semibold">{{ $user->name }}</x-skeleton.text>
                            <x-skeleton.text class="mt-1 break-all text-sm">{{ $user->email }}</x-skeleton.text>
                            @if ($actor->isAdmin())
                                <x-skeleton.text class="mt-2 text-sm">{{ $user->schools->pluck('name')->join(' / ') ?: 'Sem escola vinculada' }}</x-skeleton.text>
                            @endif
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        @if ($actor->isAdmin())
                            <x-skeleton.block class="size-10 rounded-lg" />
                        @endif
                        @can('resetPassword', $user)
                            <x-skeleton.block class="size-10 rounded-lg" />
                        @endcan
                        @if (! $user->is($actor))
                            @can('toggleActive', $user)
                                <x-skeleton.block class="size-10 rounded-lg" />
                            @endcan
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-10 text-center">
                <x-skeleton.text>Nenhum usuário encontrado.</x-skeleton.text>
            </div>
        @endforelse
    </div>
    <div class="hidden md:block">
        <x-skeleton.table :columns="$columns">
            @forelse ($users as $user)
                <tr>
                    <td class="whitespace-nowrap px-5 py-4 text-lg font-medium">
                        <div class="flex items-center gap-3">
                            <x-skeleton.block class="size-12 shrink-0 rounded-full" />
                            <x-skeleton.text>{{ $user->name }}</x-skeleton.text>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-lg"><x-skeleton.text>{{ $user->email }}</x-skeleton.text></td>
                    @if ($actor->isAdmin())
                        <td class="px-5 py-4 text-lg"><x-skeleton.text>{{ $user->schools->pluck('name')->join(' / ') ?: 'Sem escola vinculada' }}</x-skeleton.text></td>
                    @endif
                    <td class="whitespace-nowrap px-5 py-4 text-right text-lg">
                        <div class="flex justify-end gap-2">
                            @if ($actor->isAdmin())
                                <x-skeleton.block class="size-12 rounded-lg" />
                            @endif
                            @can('resetPassword', $user)
                                <x-skeleton.block class="size-12 rounded-lg" />
                            @endcan
                            @if (! $user->is($actor))
                                @can('toggleActive', $user)
                                    <x-skeleton.block class="size-12 rounded-lg" />
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ count($columns) }}" class="px-5 py-10 text-center text-lg"><x-skeleton.text>Nenhum usuário encontrado.</x-skeleton.text></td></tr>
            @endforelse
        </x-skeleton.table>
    </div>
    <x-skeleton.pagination :paginator="$users" class="mt-4" />
</div>
