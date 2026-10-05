<?php

namespace App\Livewire\Activity;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Material;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogIndex extends Component
{
    use WithPagination;

    public string $userFilter = '';

    public string $actionFilter = '';

    public string $materialFilter = '';

    public string $fromDate = '';

    public string $untilDate = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', ActivityLog::class);
    }

    public function updated(string $propertyName): void
    {
        Gate::authorize('viewAny', ActivityLog::class);

        if (in_array($propertyName, array_keys($this->filterRules()), true)) {
            $this->validateOnly($propertyName, $this->filterRules());
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        Gate::authorize('viewAny', ActivityLog::class);

        $this->reset(['userFilter', 'actionFilter', 'materialFilter', 'fromDate', 'untilDate']);
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        Gate::authorize('viewAny', ActivityLog::class);

        $this->validate($this->filterRules());

        $logs = ActivityLog::query()
            ->with(['user', 'material'])
            ->when($this->userFilter !== '', fn (Builder $query): Builder => $query->where('user_id', $this->userFilter))
            ->when($this->actionFilter !== '', fn (Builder $query): Builder => $query->where('action', $this->actionFilter))
            ->when($this->materialFilter !== '', fn (Builder $query): Builder => $query->where('material_id', $this->materialFilter))
            ->when($this->fromDate !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->untilDate !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $this->untilDate))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('livewire.activity.activity-log-index', [
            'logs' => $logs,
            'users' => User::query()->whereHas('activityLogs')->orderBy('name')->get(),
            'actions' => ActivityAction::cases(),
            'materials' => Material::query()->whereHas('activityLogs')->orderBy('title')->get(),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function filterRules(): array
    {
        return [
            'userFilter' => $this->userFilter === ''
                ? ['nullable']
                : ['required', 'integer', 'exists:users,id'],
            'actionFilter' => $this->actionFilter === ''
                ? ['nullable']
                : ['required', Rule::enum(ActivityAction::class)],
            'materialFilter' => $this->materialFilter === ''
                ? ['nullable']
                : ['required', 'integer', 'exists:materials,id'],
            'fromDate' => $this->fromDate === ''
                ? ['nullable']
                : ['required', 'date_format:Y-m-d'],
            'untilDate' => $this->untilDate === ''
                ? ['nullable']
                : array_merge(
                    ['required', 'date_format:Y-m-d'],
                    $this->fromDate === '' ? [] : ['after_or_equal:fromDate'],
                ),
        ];
    }
}
