<?php

namespace App\Livewire\Materials;

use App\Actions\Favorite\ToggleFavorite;
use App\Enums\MaterialStatus;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Models\Category;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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
    public string $sortOrder = 'title';

    #[Url]
    public string|int|null $categoryFilter = null;

    #[Url]
    public string $formatFilter = '';

    #[Url]
    public string $authorFilter = '';

    #[Url]
    public string $publishedPeriod = '';

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

    public function updatedSortOrder(): void
    {
        $this->authorizeLibrary();

        if (! array_key_exists($this->sortOrder, $this->sortOptions())) {
            $this->sortOrder = 'title';
        }

        $this->resetPage();
    }

    public function updatedFormatFilter(): void
    {
        $this->authorizeLibrary();
        $this->resetPage();
    }

    public function updatedAuthorFilter(): void
    {
        $this->authorizeLibrary();
        $this->resetPage();
    }

    public function updatedPublishedPeriod(): void
    {
        $this->authorizeLibrary();

        if (! array_key_exists($this->publishedPeriod, $this->periodOptions())) {
            $this->publishedPeriod = '';
        }

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
            'categoryFilter',
            'formatFilter',
            'authorFilter',
            'publishedPeriod',
            'sortOrder',
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
            ->where('status', MaterialStatus::PUBLISHED)
            ->when($this->search !== '', fn (Builder $query): Builder => $this->applySearch($query))
            ->when(
                $this->categoryFilter,
                fn (Builder $query): Builder => $query->where('category_id', $this->categoryFilter)
            )
            ->when(
                $this->formatFilter !== '',
                fn (Builder $query): Builder => $query->where('type', $this->formatFilter)
            )
            ->when(
                $this->authorFilter !== '',
                fn (Builder $query): Builder => $query->where('author', $this->authorFilter)
            )
            ->when(
                $this->publishedPeriod !== '',
                fn (Builder $query): Builder => $query->where(
                    'published_at',
                    '>=',
                    now()->subDays((int) $this->publishedPeriod)->startOfDay(),
                )
            )
            ->when(
                $this->sortOrder === 'most_downloaded',
                fn (Builder $query): Builder => $query->withCount('downloads'),
            );

        $materials = match ($this->sortOrder) {
            'recent' => $materials->orderByDesc('published_at')->orderByDesc('id'),
            'author' => $materials->orderBy('author')->orderBy('title'),
            'most_downloaded' => $materials->orderByDesc('downloads_count')->orderBy('title'),
            default => $materials->orderBy('title'),
        };
        $materials = $materials->paginate(15);

        foreach ($materials as $material) {
            Gate::authorize('view', $material);
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->whereHas('materials', fn (Builder $query): Builder => $query->where('status', MaterialStatus::PUBLISHED))
            ->orderBy('name')
            ->get();

        $formats = Material::query()
            ->where('status', MaterialStatus::PUBLISHED)
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        $authors = Material::query()
            ->where('status', MaterialStatus::PUBLISHED)
            ->whereNotNull('author')
            ->distinct()
            ->orderBy('author')
            ->pluck('author');

        return view('livewire.materials.material-library', [
            'materials' => $materials,
            'categories' => $categories,
            'isTeacher' => $user->isTeacher(),
            'sortOptions' => $this->sortOptions(),
            'formats' => $formats,
            'authors' => $authors,
            'periodOptions' => $this->periodOptions(),
            'recentSearches' => session()->get('material_library_recent_searches', []),
            'searchSuggestions' => $this->searchSuggestions(),
            'hasActiveFilters' => $this->hasActiveFilters(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return [
            'title' => 'Título A-Z',
            'recent' => 'Mais recentes',
            'author' => 'Autor',
            'most_downloaded' => 'Mais baixados',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function periodOptions(): array
    {
        return [
            '' => 'Qualquer período',
            '7' => 'Últimos 7 dias',
            '30' => 'Últimos 30 dias',
            '90' => 'Últimos 90 dias',
            '365' => 'Último ano',
        ];
    }

    private function applySearch(Builder $query): Builder
    {
        $search = trim($this->search);

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $booleanSearch = collect(preg_split('/\s+/u', $search) ?: [])
                ->map(fn (string $term): string => preg_replace('/[^\pL\pN_-]/u', '', $term) ?? '')
                ->filter()
                ->map(fn (string $term): string => '+'.$term.'*')
                ->implode(' ');
            $searchPattern = '%'.addcslashes($search, '%_\\').'%';

            return $query->where(function (Builder $query) use ($booleanSearch, $searchPattern): void {
                $query
                    ->whereFullText(
                        ['title', 'author', 'description'],
                        $booleanSearch,
                        ['mode' => 'boolean'],
                    )
                    ->orWhere('title', 'like', $searchPattern)
                    ->orWhere('author', 'like', $searchPattern)
                    ->orWhere('description', 'like', $searchPattern);
            });
        }

        $searchPattern = '%'.addcslashes(Str::lower($search), '%_\\').'%';

        return $query->where(function (Builder $query) use ($searchPattern): void {
            $query
                ->whereRaw('LOWER(title) LIKE ? ESCAPE \'\\\'', [$searchPattern])
                ->orWhereRaw('LOWER(author) LIKE ? ESCAPE \'\\\'', [$searchPattern])
                ->orWhereRaw('LOWER(description) LIKE ? ESCAPE \'\\\'', [$searchPattern]);
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
            || filled($this->categoryFilter)
            || $this->formatFilter !== ''
            || $this->authorFilter !== ''
            || $this->publishedPeriod !== ''
            || $this->sortOrder !== 'title';
    }

    private function authorizeLibrary(): void
    {
        Gate::authorize('browseLibrary', Material::class);
    }
}
