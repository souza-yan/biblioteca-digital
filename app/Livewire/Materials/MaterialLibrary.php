<?php

namespace App\Livewire\Materials;

use App\Actions\Favorite\ToggleFavorite;
use App\Enums\MaterialStatus;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

class MaterialLibrary extends Component
{
    use Interactions;
    use InteractsWithCurrentUser;
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $titleSortDirection = 'asc';

    #[Url]
    public string|int|null $categoryFilter = null;

    #[Url]
    public string $authorFilter = '';

    public function mount(): void
    {
        $this->authorizeLibrary();
    }

    public function updatedSearch(): void
    {
        $this->authorizeLibrary();
        $this->rememberSearch();
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->authorizeLibrary();
        $this->resetPage();
    }

    public function updatedTitleSortDirection(): void
    {
        $this->authorizeLibrary();

        if (! in_array($this->titleSortDirection, ['asc', 'desc'], true)) {
            $this->titleSortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedAuthorFilter(): void
    {
        $this->authorizeLibrary();
        $this->resetPage();
    }

    public function toggleTitleSort(): void
    {
        $this->authorizeLibrary();
        $this->titleSortDirection = $this->titleSortDirection === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    public function applyRecentSearch(string $search): void
    {
        $this->authorizeLibrary();
        $this->search = Str::limit(trim($search), 100, '');
        $this->rememberSearch();
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->authorizeLibrary();
        $this->reset([
            'search',
            'authorFilter',
            'titleSortDirection',
        ]);
        $this->resetPage();
    }

    public function toggleFavorite(int $materialId, ToggleFavorite $toggleFavorite): void
    {
        $material = Material::query()->findOrFail($materialId);
        Gate::authorize('favorite', $material);

        $user = $this->currentUser();

        $toggleFavorite->handle($user, $material);

        $isFavorited = $user->favorites()->whereKey($material->getKey())->exists();

        $this->toast()
            ->success(
                $isFavorited ? 'Material favoritado!' : 'Material removido dos favoritos!',
                $isFavorited
                    ? 'O material foi adicionado aos seus favoritos.'
                    : 'O material foi removido dos seus favoritos.'
            )
            ->send();
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $this->authorizeLibrary();

        $user = $this->currentUser();
        $userId = $user->getKey();

        $materials = Material::query()
            ->with(['category', 'currentVersion'])
            ->withExists([
                'favoritedBy as is_favorited' => fn (Builder $query): Builder => $query->whereKey($userId),
            ])
            ->published()
            ->when($this->search !== '', fn (Builder $query): Builder => $this->applySearch($query))
            ->when(
                $this->categoryFilter,
                fn (Builder $query): Builder => $query->where('category_id', $this->categoryFilter)
            )
            ->when(
                $this->authorFilter !== '',
                fn (Builder $query): Builder => $query->where('author', $this->authorFilter)
            );

        $materials = $materials
            ->orderBy('title', $this->titleSortDirection)
            ->paginate(15);

        $authors = Material::query()
            ->where('status', MaterialStatus::PUBLISHED)
            ->whereNotNull('author')
            ->distinct()
            ->orderBy('author')
            ->pluck('author');

        return view('livewire.materials.material-library', [
            'materials' => $materials,
            'isTeacher' => $user->isTeacher(),
            'authors' => $authors,
            'recentSearches' => session()->get('material_library_recent_searches', []),
            'searchSuggestions' => $this->searchSuggestions(),
            'hasActiveFilters' => $this->hasActiveFilters(),
        ]);
    }

    private function applySearch(Builder $query): Builder
    {
        $searchTerm = str_replace(
            ['!', '%', '_'],
            ['!!', '!%', '!_'],
            Str::lower(trim($this->search)),
        );
        $searchPattern = '%'.$searchTerm.'%';

        return $query->where(function (Builder $query) use ($searchPattern): void {
            $query
                ->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$searchPattern])
                ->orWhereRaw("LOWER(author) LIKE ? ESCAPE '!'", [$searchPattern]);
        });
    }

    /**
     * @return array<int, string>
     */
    private function searchSuggestions(): array
    {
        if (Str::length(trim($this->search)) < 2) {
            return [];
        }

        $searchPattern = '%'.addcslashes($this->search, '%_\\').'%';

        return Material::query()
            ->where('status', MaterialStatus::PUBLISHED)
            ->where(fn (Builder $query): Builder => $query
                ->where('title', 'like', $searchPattern)
                ->orWhere('author', 'like', $searchPattern))
            ->limit(5)
            ->get(['title', 'author'])
            ->flatMap(fn (Material $material): array => [$material->title, $material->author])
            ->filter()
            ->unique()
            ->take(5)
            ->values()
            ->all();
    }

    private function rememberSearch(): void
    {
        $search = trim($this->search);

        if (Str::length($search) < 3) {
            return;
        }

        $recentSearches = collect(session()->get('material_library_recent_searches', []))
            ->reject(fn (string $recentSearch): bool => Str::lower($recentSearch) === Str::lower($search))
            ->prepend($search)
            ->take(5)
            ->values()
            ->all();

        session()->put('material_library_recent_searches', $recentSearches);
    }

    private function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || $this->authorFilter !== ''
            || $this->titleSortDirection !== 'asc';
    }

    private function authorizeLibrary(): void
    {
        Gate::authorize('browseLibrary', Material::class);
    }
}
