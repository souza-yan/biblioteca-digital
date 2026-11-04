<?php

namespace App\Livewire\Materials;

use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

class HomeMaterialSearch extends Component
{
    use InteractsWithCurrentUser;

    public string $search = '';

    public function mount(): void
    {
        $this->authorizeSearch();
    }

    public function render(): View
    {
        $this->authorizeSearch();

        $search = trim($this->search);

        $materials = Material::query()
            ->with(['category', 'currentVersion'])
            ->published()
            ->when($search !== '', fn (Builder $query): Builder => $this->applySearch($query, $search))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('livewire.materials.home-material-search', [
            'materials' => $materials,
            'normalizedSearch' => $search,
        ]);
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        $searchTerm = str_replace(
            ['!', '%', '_'],
            ['!!', '!%', '!_'],
            Str::lower($search),
        );
        $searchPattern = '%'.$searchTerm.'%';

        return $query->where(function (Builder $query) use ($searchPattern): void {
            $query
                ->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$searchPattern])
                ->orWhereRaw("LOWER(author) LIKE ? ESCAPE '!'", [$searchPattern]);
        });
    }

    private function authorizeSearch(): void
    {
        Gate::authorize('browseLibrary', Material::class);
        $this->currentUser();
    }
}
