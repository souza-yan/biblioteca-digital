<?php

namespace App\Livewire\Materials;

use App\Actions\Activity\LogActivity;
use App\Actions\Favorite\ToggleFavorite;
use App\Actions\Material\UpdateMaterial;
use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Livewire\Forms\MaterialForm;
use App\Models\Category;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
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

    public bool $showMobileMaterialDetail = false;

    public ?int $mobileMaterialId = null;

    public MaterialForm $editForm;

    public bool $showEditForm = false;

    #[Locked]
    public ?int $editingMaterialId = null;

    public function mount(): void
    {
        $this->authorizeLibrary();
    }

    public function placeholder(): View
    {
        return view('livewire.materials.material-library-placeholder');
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

    public function openMobileMaterialDetail(int $materialId, LogActivity $logActivity): void
    {
        $material = Material::query()
            ->with(['category', 'currentVersion'])
            ->findOrFail($materialId);

        Gate::authorize('view', $material);

        $user = $this->currentUser();

        if ($user->isTeacher()) {
            $logActivity->handle(
                $user,
                ActivityAction::MATERIAL_PREVIEWED,
                'Material consultado.',
                $material,
            );
        }

        $this->mobileMaterialId = $material->getKey();
        $this->showMobileMaterialDetail = true;
    }

    public function closeMobileMaterialDetail(): void
    {
        $this->showMobileMaterialDetail = false;
        $this->mobileMaterialId = null;
    }

    public function openEditMaterial(int $materialId): void
    {
        $material = $this->findEditableMaterial($materialId);

        $this->resetValidation();
        $this->editingMaterialId = $material->getKey();
        $this->editForm->materialId = $material->getKey();
        $this->editForm->title = $material->title;
        $this->editForm->description = $material->description ?? '';
        $this->editForm->category_id = (string) $material->category_id;
        $this->editForm->type = $material->type;
        $this->editForm->author = $material->author;
        $this->showEditForm = true;
    }

    public function closeEditMaterial(): void
    {
        $this->resetEditForm();
    }

    public function saveEditMaterial(UpdateMaterial $updateMaterial): void
    {
        abort_unless($this->editingMaterialId !== null, 404);

        $material = $this->findEditableMaterial($this->editingMaterialId);
        $this->editForm->materialId = $material->getKey();
        $this->editForm->type = $material->type;
        $attributes = $this->editForm->validate();

        $updateMaterial->handle($this->currentUser(), $material, $attributes);

        $this->resetEditForm();

        $this->toast()
            ->success('Material atualizado!', 'As alterações foram salvas corretamente.')
            ->send();
    }

    #[Layout('layouts.app', ['usesElementSkeletons' => true])]
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
        $categories = Category::query()
            ->where('is_active', true)
            ->whereHas('materials', fn (Builder $query): Builder => $query->published())
            ->orderBy('name')
            ->get();
        $mobileMaterial = $this->showMobileMaterialDetail && $this->mobileMaterialId !== null
            ? Material::query()
                ->with(['category', 'currentVersion'])
                ->findOrFail($this->mobileMaterialId)
            : null;

        if ($mobileMaterial instanceof Material) {
            Gate::authorize('view', $mobileMaterial);
        }

        return view('livewire.materials.material-library', [
            'materials' => $materials,
            'isTeacher' => $user->isTeacher(),
            'authors' => $authors,
            'categories' => $categories,
            'activeCategories' => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'mobileMaterial' => $mobileMaterial,
            'mobilePreviewUrl' => $mobileMaterial instanceof Material
                ? ($user->isTeacher()
                    ? route('previews.show', ['material' => $mobileMaterial, 'access_recorded' => 1])
                    : route('previews.show', $mobileMaterial))
                : null,
            'recentSearches' => session()->get('material_library_recent_searches', []),
            'searchSuggestions' => $this->searchSuggestions(),
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

    private function findEditableMaterial(int $materialId): Material
    {
        $material = Material::query()->findOrFail($materialId);

        Gate::authorize('update', $material);

        return $material;
    }

    private function resetEditForm(): void
    {
        $this->editForm->reset();
        $this->editingMaterialId = null;
        $this->showEditForm = false;
        $this->resetValidation();
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

    private function authorizeLibrary(): void
    {
        Gate::authorize('browseLibrary', Material::class);
    }
}
