<?php

namespace App\Livewire\Materials;

use App\Enums\MaterialStatus;
use App\Models\Category;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class MaterialLibrary extends Component
{
    use WithPagination;

    public string $search = '';

    public string $categoryFilter = '';

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

    #[Layout('layouts.app')]
    public function render(): View
    {
        $this->authorizeLibrary();

        $materials = Material::query()
            ->with('category')
            ->where('status', MaterialStatus::PUBLISHED)
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('author', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->categoryFilter !== '', fn (Builder $query): Builder => $query->where('category_id', $this->categoryFilter))
            ->orderBy('title')
            ->paginate(15);

        foreach ($materials as $material) {
            Gate::authorize('view', $material);
        }

        $categories = Category::query()
            ->whereHas('materials', fn (Builder $query): Builder => $query->where('status', MaterialStatus::PUBLISHED))
            ->orderBy('name')
            ->get();

        return view('livewire.materials.material-library', [
            'materials' => $materials,
            'categories' => $categories,
        ]);
    }

    private function authorizeLibrary(): void
    {
        Gate::authorize('browseLibrary', Material::class);
    }
}
