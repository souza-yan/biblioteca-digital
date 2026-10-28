<?php

namespace App\Livewire\Activity;

use App\Enums\ActivityAction;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Models\ActivityLog;
use App\Models\Download;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class MyHistory extends Component
{
    use InteractsWithCurrentUser;
    use WithPagination;

    #[Url]
    public string $activeTab = 'downloads';

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(): void
    {
        $user = $this->currentUser();
        abort_unless($user->isTeacher(), 403);

        $this->normalizeActiveTab();
    }

    public function updatedActiveTab(): void
    {
        $this->normalizeActiveTab();
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $user = $this->currentUser();

        $records = $this->activeTab === 'downloads'
            ? Download::query()
                ->with(['material.category', 'version'])
                ->whereBelongsTo($user)
                ->when($this->search !== '', fn (Builder $query): Builder => $query
                    ->whereHas('material', fn (Builder $query): Builder => $query
                        ->where('title', 'like', '%'.$this->search.'%')))
                ->orderByDesc('downloaded_at')
                ->orderByDesc('id')
                ->paginate(15)
            : ActivityLog::query()
                ->with('material.category')
                ->whereBelongsTo($user)
                ->where('action', ActivityAction::MATERIAL_PREVIEWED)
                ->whereNotNull('material_id')
                ->when($this->search !== '', fn (Builder $query): Builder => $query
                    ->whereHas('material', fn (Builder $query): Builder => $query
                        ->where('title', 'like', '%'.$this->search.'%')))
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(15);

        return view('livewire.activity.my-history', [
            'records' => $records,
        ]);
    }

    private function normalizeActiveTab(): void
    {
        if (! in_array($this->activeTab, ['downloads', 'accesses'], true)) {
            $this->activeTab = 'downloads';
        }
    }
}
