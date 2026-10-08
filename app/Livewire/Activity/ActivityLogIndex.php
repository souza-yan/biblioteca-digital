<?php

namespace App\Livewire\Activity;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Download;
use App\Models\Material;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogIndex extends Component
{
    use WithPagination;

    /**
     * @var array<string, array{
     *     label: string,
     *     clearMethod: string,
     *     filterGridColumns: string,
     *     headerStyle: string,
     *     filters: array<int, array<string, string>>,
     *     columns: array<int, array{label: string, nowrap?: bool}>
     * }>
     */
    private const TAB_PRESENTATION = [
        'downloads' => [
            'label' => 'Downloads',
            'clearMethod' => 'clearDownloadsFilters',
            'filterGridColumns' => 'md:grid-cols-2 xl:grid-cols-4',
            'headerStyle' => 'blue',
            'filters' => [
                ['label' => 'Usuário', 'model' => 'downloadsUserFilter', 'type' => 'select', 'placeholder' => 'Todos os usuários', 'options' => 'users'],
                ['label' => 'Material', 'model' => 'downloadsMaterialFilter', 'type' => 'select', 'placeholder' => 'Todos os materiais', 'options' => 'materials'],
                ['label' => 'De', 'model' => 'downloadsFromDate', 'type' => 'date'],
                ['label' => 'Até', 'model' => 'downloadsUntilDate', 'type' => 'date'],
            ],
            'columns' => [
                ['label' => 'Data e hora', 'nowrap' => true],
                ['label' => 'Usuário'],
                ['label' => 'Material'],
                ['label' => 'Versão'],
                ['label' => 'Categoria'],
            ],
        ],
        'changes' => [
            'label' => 'Cadastros e alterações',
            'clearMethod' => 'clearChangesFilters',
            'filterGridColumns' => 'md:grid-cols-2 xl:grid-cols-4',
            'headerStyle' => 'blue',
            'filters' => [
                ['label' => 'Usuário', 'model' => 'changesUserFilter', 'type' => 'select', 'placeholder' => 'Todos os usuários', 'options' => 'users'],
                ['label' => 'Ação', 'model' => 'changesActionFilter', 'type' => 'select', 'placeholder' => 'Todas as ações', 'options' => 'actions'],
                ['label' => 'De', 'model' => 'changesFromDate', 'type' => 'date'],
                ['label' => 'Até', 'model' => 'changesUntilDate', 'type' => 'date'],
            ],
            'columns' => [
                ['label' => 'Data e hora', 'nowrap' => true],
                ['label' => 'Quem fez'],
                ['label' => 'Ação'],
                ['label' => 'O que foi afetado'],
            ],
        ],
        'accesses' => [
            'label' => 'Acessos',
            'clearMethod' => 'clearAccessesFilters',
            'filterGridColumns' => 'md:grid-cols-3',
            'headerStyle' => 'blue',
            'filters' => [
                ['label' => 'Usuário', 'model' => 'accessesUserFilter', 'type' => 'select', 'placeholder' => 'Todos os usuários', 'options' => 'users'],
                ['label' => 'De', 'model' => 'accessesFromDate', 'type' => 'date'],
                ['label' => 'Até', 'model' => 'accessesUntilDate', 'type' => 'date'],
            ],
            'columns' => [
                ['label' => 'Data e hora', 'nowrap' => true],
                ['label' => 'Usuário'],
                ['label' => 'Ação'],
                ['label' => 'Descrição'],
            ],
        ],
    ];

    #[Url]
    public string $activeTab = 'downloads';

    public string $downloadsUserFilter = '';

    public string $downloadsMaterialFilter = '';

    public string $downloadsFromDate = '';

    public string $downloadsUntilDate = '';

    public string $changesUserFilter = '';

    public string $changesActionFilter = '';

    public string $changesFromDate = '';

    public string $changesUntilDate = '';

    public string $accessesUserFilter = '';

    public string $accessesFromDate = '';

    public string $accessesUntilDate = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', ActivityLog::class);

        if (! in_array($this->activeTab, $this->tabs(), true)) {
            $this->activeTab = 'downloads';
        }
    }

    public function updated(string $propertyName): void
    {
        Gate::authorize('viewAny', ActivityLog::class);

        if ($propertyName === 'activeTab') {
            if (! in_array($this->activeTab, $this->tabs(), true)) {
                $this->activeTab = 'downloads';
            }

            return;
        }

        $rules = $this->filterRules();

        if (! array_key_exists($propertyName, $rules)) {
            return;
        }

        $this->validateOnly($propertyName, $rules);
        $this->resetPage($this->paginatorForFilter($propertyName));
    }

    public function selectTab(string $tab): void
    {
        Gate::authorize('viewAny', ActivityLog::class);
        abort_unless(in_array($tab, $this->tabs(), true), 404);

        $this->activeTab = $tab;
    }

    public function clearDownloadsFilters(): void
    {
        Gate::authorize('viewAny', ActivityLog::class);
        $this->reset(['downloadsUserFilter', 'downloadsMaterialFilter', 'downloadsFromDate', 'downloadsUntilDate']);
        $this->resetPage('downloadsPage');
    }

    public function clearChangesFilters(): void
    {
        Gate::authorize('viewAny', ActivityLog::class);
        $this->reset(['changesUserFilter', 'changesActionFilter', 'changesFromDate', 'changesUntilDate']);
        $this->resetPage('changesPage');
    }

    public function clearAccessesFilters(): void
    {
        Gate::authorize('viewAny', ActivityLog::class);
        $this->reset(['accessesUserFilter', 'accessesFromDate', 'accessesUntilDate']);
        $this->resetPage('accessesPage');
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        Gate::authorize('viewAny', ActivityLog::class);

        $this->validate($this->filterRules());

        $records = match ($this->activeTab) {
            'downloads' => $this->downloadsQuery()->paginate(15, ['*'], 'downloadsPage'),
            'changes' => $this->changesQuery()->paginate(15, ['*'], 'changesPage'),
            'accesses' => $this->accessesQuery()->paginate(15, ['*'], 'accessesPage'),
            default => abort(404),
        };

        $users = User::query()->orderBy('name')->get(['id', 'name']);
        $materials = Material::query()->orderBy('title')->get(['id', 'title']);
        $tabPresentation = self::TAB_PRESENTATION[$this->activeTab];
        $filterOptions = [
            'users' => $users->mapWithKeys(
                static fn (User $user): array => [(string) $user->getKey() => $user->name],
            )->all(),
            'materials' => $materials->mapWithKeys(
                static fn (Material $material): array => [(string) $material->getKey() => $material->title],
            )->all(),
            'actions' => collect($this->changeActions())
                ->mapWithKeys(
                    static fn (ActivityAction $action): array => [
                        $action->value => $action->label(),
                    ],
                )
                ->all(),
        ];
        $tabLabels = $this->tabLabels();

        return view('livewire.activity.activity-log-index', [
            'records' => $records,
            'filterFields' => $tabPresentation['filters'],
            'filterOptions' => $filterOptions,
            'filterGridColumns' => $tabPresentation['filterGridColumns'],
            'clearFiltersMethod' => $tabPresentation['clearMethod'],
            'columns' => $tabPresentation['columns'],
            'tableHeaderStyle' => $tabPresentation['headerStyle'],
            'actionBadgeClasses' => $this->actionBadgeClasses(),
            'tabLabels' => $tabLabels,
            'activeTabLabel' => $tabPresentation['label'],
            'emptyMessage' => 'Nenhum registro encontrado',
        ]);
    }

    /**
     * @return Builder<Download>
     */
    private function downloadsQuery(): Builder
    {
        return Download::query()
            ->with(['user', 'material.category', 'version'])
            ->when($this->downloadsUserFilter !== '', fn (Builder $query): Builder => $query->where('user_id', $this->downloadsUserFilter))
            ->when($this->downloadsMaterialFilter !== '', fn (Builder $query): Builder => $query->where('material_id', $this->downloadsMaterialFilter))
            ->when($this->downloadsFromDate !== '', fn (Builder $query): Builder => $query->whereDate('downloaded_at', '>=', $this->downloadsFromDate))
            ->when($this->downloadsUntilDate !== '', fn (Builder $query): Builder => $query->whereDate('downloaded_at', '<=', $this->downloadsUntilDate))
            ->orderByDesc('downloaded_at')
            ->orderByDesc('id');
    }

    /**
     * @return Builder<ActivityLog>
     */
    private function changesQuery(): Builder
    {
        $actions = array_map(
            static fn (ActivityAction $action): string => $action->value,
            $this->changeActions(),
        );

        return ActivityLog::query()
            ->with(['user', 'material'])
            ->whereIn('action', $actions)
            ->when($this->changesUserFilter !== '', fn (Builder $query): Builder => $query->where('user_id', $this->changesUserFilter))
            ->when($this->changesActionFilter !== '', fn (Builder $query): Builder => $query->where('action', $this->changesActionFilter))
            ->when($this->changesFromDate !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $this->changesFromDate))
            ->when($this->changesUntilDate !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $this->changesUntilDate))
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * @return Builder<ActivityLog>
     */
    private function accessesQuery(): Builder
    {
        $actions = array_map(
            static fn (ActivityAction $action): string => $action->value,
            $this->accessActions(),
        );

        return ActivityLog::query()
            ->with(['user', 'material'])
            ->whereIn('action', $actions)
            ->when($this->accessesUserFilter !== '', fn (Builder $query): Builder => $query->where('user_id', $this->accessesUserFilter))
            ->when($this->accessesFromDate !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $this->accessesFromDate))
            ->when($this->accessesUntilDate !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $this->accessesUntilDate))
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function filterRules(): array
    {
        return [
            'downloadsUserFilter' => $this->idFilterRules($this->downloadsUserFilter, 'users'),
            'downloadsMaterialFilter' => $this->idFilterRules($this->downloadsMaterialFilter, 'materials'),
            'downloadsFromDate' => $this->dateFilterRules($this->downloadsFromDate),
            'downloadsUntilDate' => $this->dateFilterRules($this->downloadsUntilDate),
            'changesUserFilter' => $this->idFilterRules($this->changesUserFilter, 'users'),
            'changesActionFilter' => $this->changesActionFilter === ''
                ? ['nullable']
                : ['required', Rule::in(array_map(
                    static fn (ActivityAction $action): string => $action->value,
                    $this->changeActions(),
                ))],
            'changesFromDate' => $this->dateFilterRules($this->changesFromDate),
            'changesUntilDate' => $this->dateFilterRules($this->changesUntilDate),
            'accessesUserFilter' => $this->idFilterRules($this->accessesUserFilter, 'users'),
            'accessesFromDate' => $this->dateFilterRules($this->accessesFromDate),
            'accessesUntilDate' => $this->dateFilterRules($this->accessesUntilDate),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function idFilterRules(string $value, string $table): array
    {
        return $value === ''
            ? ['nullable']
            : ['required', 'integer', Rule::exists($table, 'id')];
    }

    /**
     * @return array<int, mixed>
     */
    private function dateFilterRules(string $value): array
    {
        return $value === ''
            ? ['nullable']
            : ['required', 'date_format:Y-m-d'];
    }

    private function paginatorForFilter(string $propertyName): string
    {
        return match (true) {
            str_starts_with($propertyName, 'downloads') => 'downloadsPage',
            str_starts_with($propertyName, 'changes') => 'changesPage',
            default => 'accessesPage',
        };
    }

    /**
     * @return array<int, string>
     */
    private function tabs(): array
    {
        return array_keys(self::TAB_PRESENTATION);
    }

    /**
     * @return array<string, string>
     */
    private function tabLabels(): array
    {
        return array_map(
            static fn (array $tab): string => $tab['label'],
            self::TAB_PRESENTATION,
        );
    }

    /**
     * @return array<int, ActivityAction>
     */
    private function changeActions(): array
    {
        return [
            ActivityAction::USER_CREATED,
            ActivityAction::USER_UPDATED,
            ActivityAction::USER_TOGGLED,
            ActivityAction::CATEGORY_CREATED,
            ActivityAction::CATEGORY_UPDATED,
            ActivityAction::CATEGORY_TOGGLED,
            ActivityAction::MATERIAL_CREATED,
            ActivityAction::MATERIAL_UPDATED,
            ActivityAction::MATERIAL_PUBLISHED,
            ActivityAction::MATERIAL_ARCHIVED,
            ActivityAction::VERSION_CREATED,
        ];
    }

    /**
     * @return array<int, ActivityAction>
     */
    private function accessActions(): array
    {
        return [
            ActivityAction::AUTH_LOGIN,
            ActivityAction::AUTH_LOGOUT,
            ActivityAction::MATERIAL_PREVIEWED,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function actionBadgeClasses(): array
    {
        return [
            ActivityAction::USER_CREATED->value => 'bg-blue-100 text-blue-800',
            ActivityAction::USER_UPDATED->value => 'bg-blue-100 text-blue-800',
            ActivityAction::USER_TOGGLED->value => 'bg-blue-100 text-blue-800',
            ActivityAction::CATEGORY_CREATED->value => 'bg-cyan-100 text-cyan-800',
            ActivityAction::CATEGORY_UPDATED->value => 'bg-cyan-100 text-cyan-800',
            ActivityAction::CATEGORY_TOGGLED->value => 'bg-cyan-100 text-cyan-800',
            ActivityAction::MATERIAL_CREATED->value => 'bg-green-100 text-green-800',
            ActivityAction::MATERIAL_UPDATED->value => 'bg-amber-100 text-amber-800',
            ActivityAction::MATERIAL_PUBLISHED->value => 'bg-emerald-100 text-emerald-800',
            ActivityAction::MATERIAL_ARCHIVED->value => 'bg-rose-100 text-rose-800',
            ActivityAction::VERSION_CREATED->value => 'bg-violet-100 text-violet-800',
            ActivityAction::AUTH_LOGIN->value => 'bg-emerald-100 text-emerald-800',
            ActivityAction::AUTH_LOGOUT->value => 'bg-gray-100 text-gray-700',
            ActivityAction::MATERIAL_PREVIEWED->value => 'bg-indigo-100 text-indigo-800',
        ];
    }
}
