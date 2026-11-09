<div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8" data-skeleton-variant="{{ $isTeacher ? 'teacher' : 'management' }}">
    <article class="rounded-md border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start sm:gap-4">
            <div>
                @if (! $isTeacher)<x-skeleton.block class="mb-3 size-9 rounded-lg sm:hidden" />@endif
                <x-skeleton.text class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $material->title }}</x-skeleton.text>
                @if ($isTeacher)<x-skeleton.text class="mt-1 w-fit text-sm sm:text-base">Por {{ $material->author }}</x-skeleton.text>@endif
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2 sm:flex-nowrap">
                @if ($isTeacher)
                    <x-skeleton.block class="size-10 rounded-lg sm:size-12" />
                    @if ($material->currentVersion)
                        <x-skeleton.block class="size-10 rounded-lg sm:size-12" />
                        <x-skeleton.block class="size-10 rounded-lg sm:hidden" />
                    @endif
                @endif
            </div>
        </div>
        <div class="mt-4 grid grid-cols-1 gap-2 border-t border-gray-100 pt-4 sm:mt-6 sm:grid-cols-2 sm:gap-4 sm:pt-5">
            <div><x-skeleton.text class="w-fit text-xs font-semibold uppercase">Status</x-skeleton.text><x-skeleton.text class="mt-1 w-fit rounded-full px-3 py-1 text-sm font-semibold">{{ $material->status->label() }}</x-skeleton.text></div>
            @if ($isTeacher)
                <div class="hidden sm:block"><x-skeleton.line size="xs" class="w-20" /><x-skeleton.text class="mt-1 w-fit text-sm sm:text-base">{{ $material->creator->name }}</x-skeleton.text></div>
            @endif
            <div class="sm:col-span-2"><x-skeleton.line size="xs" class="w-20" /><x-skeleton.text class="mt-1 whitespace-pre-line text-sm sm:text-base">{{ $material->description ?: 'Sem descrição.' }}</x-skeleton.text></div>
            @if ($isTeacher && $material->currentVersion)
                <div class="hidden sm:block"><x-skeleton.line size="xs" class="w-24" /><x-skeleton.text class="mt-1 text-sm sm:text-base">{{ $material->currentVersion->version_number }} · {{ $material->currentVersion->original_name }}</x-skeleton.text></div>
            @endif
        </div>
        @if ($isTeacher && $material->currentVersion)
            <div class="mt-4 border-t border-gray-100 pt-4 sm:mt-6 sm:pt-5" data-skeleton-section="file-preview">
                <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-3 sm:gap-3">
                    <div class="sm:col-span-3"><x-skeleton.text class="w-fit font-semibold">Nome do arquivo</x-skeleton.text><x-skeleton.text class="break-all">{{ $material->currentVersion->original_name }}</x-skeleton.text></div>
                    <div><x-skeleton.text class="w-fit font-semibold">Tipo</x-skeleton.text><x-skeleton.text class="break-all">{{ $material->currentVersion->mime_type }}</x-skeleton.text></div>
                    <div><x-skeleton.text class="w-fit font-semibold">Tamanho</x-skeleton.text><x-skeleton.text class="w-fit">{{ number_format($material->currentVersion->size / 1024, 1) }} KB</x-skeleton.text></div>
                    <div class="hidden items-end justify-end sm:flex"><x-skeleton.block class="h-10 w-48 rounded-md" /></div>
                </div>
            </div>
        @endif
    </article>
    @if ($canViewVersionHistory)
        <section class="mt-6 flex flex-col rounded-md border border-gray-200 bg-white p-4 shadow-sm sm:p-6" data-skeleton-section="version-history">
            <x-skeleton.text class="order-1 w-fit text-lg font-semibold">Versões</x-skeleton.text>
            <div class="order-3 mt-4 hidden overflow-x-auto sm:order-2 sm:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        @foreach (['Versão', 'Arquivo', 'Tipo', 'Tamanho', 'Nota', 'Publicado por', 'Data', 'Ação'] as $label)
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase"><x-skeleton.text>{{ $label }}</x-skeleton.text></th>
                        @endforeach
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($versions as $version)
                            <tr>
                                @foreach ([$version->version_number, $version->original_name, $version->mime_type, number_format($version->size / 1024, 1).' KB', $version->change_note ?: '—', $version->publisher?->name ?? '—', $version->created_at?->format('d/m/Y H:i')] as $value)
                                    <td @class(['px-4 py-3 text-sm', 'whitespace-nowrap' => $loop->last])><x-skeleton.text>{{ $value }}</x-skeleton.text></td>
                                @endforeach
                                <td class="px-4 py-3 text-right text-sm">
                                    @if ($version->isNot($material->currentVersion) && in_array($version->mime_type, ['application/pdf', 'image/png', 'image/jpeg', 'image/webp', 'image/gif', 'text/plain', 'video/mp4', 'video/webm', 'audio/mpeg'], true))
                                        <x-skeleton.block class="ml-auto size-12 rounded-lg" />
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-6"><x-skeleton.line class="mx-auto w-48" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="order-3 mt-4 space-y-3 sm:hidden">
                @forelse ($versions as $version)
                    <article class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0"><x-skeleton.text class="w-fit text-sm font-semibold">Versão {{ $version->version_number }}</x-skeleton.text><x-skeleton.text class="mt-1 truncate text-sm">{{ $version->original_name }}</x-skeleton.text></div>
                            @if ($version->isNot($material->currentVersion) && in_array($version->mime_type, ['application/pdf', 'image/png', 'image/jpeg', 'image/webp', 'image/gif', 'text/plain', 'video/mp4', 'video/webm', 'audio/mpeg'], true))
                                <x-skeleton.block class="size-10 shrink-0 rounded-lg" />
                            @endif
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs"><x-skeleton.badge /><x-skeleton.line size="xs" class="w-16" /><x-skeleton.line size="xs" class="w-20" /></div>
                        @if ($version->change_note)<div class="mt-3 border-t border-slate-200 pt-3"><x-skeleton.text class="text-sm leading-5">{{ $version->change_note }}</x-skeleton.text></div>@endif
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 px-4 py-6"><x-skeleton.line class="mx-auto w-48" /></div>
                @endforelse
            </div>
            @if ($canManageVersions)
                <div class="order-2 mt-4 space-y-4 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:order-4 sm:mt-8 sm:space-y-5 sm:p-6" data-skeleton-section="version-form">
                    <div>
                        <div class="flex items-center gap-3"><x-skeleton.block class="size-10 shrink-0 rounded-xl" /><div><x-skeleton.text class="w-fit text-base font-semibold">Enviar nova versão</x-skeleton.text><x-skeleton.text class="mt-0.5 text-sm">O arquivo atual será substituído sem apagar o histórico.</x-skeleton.text></div></div>
                    </div>
                    <div>
                        <x-skeleton.text class="mb-1 w-fit text-base font-medium">Arquivo *</x-skeleton.text>
                        <div class="rounded-xl border border-gray-300 bg-white p-2">
                            <x-skeleton.block class="h-11 rounded-lg" />
                            <div class="px-1 pt-2"><x-skeleton.text class="w-fit text-sm">Nenhum arquivo selecionado</x-skeleton.text></div>
                        </div>
                    </div>
                    <div><x-skeleton.line class="w-36" /><x-skeleton.block class="mt-1 h-10 rounded-lg" /></div>
                    <div class="flex justify-end border-t border-slate-200 pt-4"><div class="flex w-full items-center justify-center gap-2 rounded-lg bg-slate-200 px-5 py-2.5 text-sm font-semibold sm:w-auto"><span class="size-5"></span><span class="invisible">Salvar versão</span></div></div>
                </div>
            @endif
        </section>
    @endif
</div>
