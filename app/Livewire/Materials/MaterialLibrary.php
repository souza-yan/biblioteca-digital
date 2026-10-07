<?php

namespace App\Livewire\Materials;

use App\Actions\Favorite\ToggleFavorite;
use App\Enums\MaterialStatus;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Models\Category;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class MaterialLibrary extends Component
{
    use InteractsWithCurrentUser;
    use WithPagination;

    public string $search = '';

    public string $sortOrder = 'title';

    #[Url]
    public string|int|null $categoryFilter = null;

    public function mount(): void
    {
        $this->authorizeLibrary();
    }

    public function updatedSearch(): void
    {
        $this->authorizeLibrary();
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

    public function toggleFavorite(int $materialId, ToggleFavorite $toggleFavorite): void
    {
        $material = Material::query()->findOrFail($materialId);
        Gate::authorize('favorite', $material);

        $user = $this->currentUser();

        $toggleFavorite->handle($user, $material);
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
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query
                        ->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('author', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when(
                $this->categoryFilter,
                fn (Builder $query): Builder => $query->where('category_id', $this->categoryFilter)
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

        return view('livewire.materials.material-library', [
            'materials' => $materials,
            'categories' => $categories,
            'isTeacher' => $user->isTeacher(),
            'sortOptions' => $this->sortOptions(),
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

    private function authorizeLibrary(): void
    {
        Gate::authorize('browseLibrary', Material::class);
    }
}
