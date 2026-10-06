<?php

namespace App\Livewire\Categories;

use App\Enums\MaterialStatus;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryLibrary extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Category::class);
    }

    public function updatedSearch(): void
    {
        Gate::authorize('viewAny', Category::class);
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        Gate::authorize('viewAny', Category::class);

        $categories = Category::query()
            ->withCount([
                'materials as published_materials_count' => fn (Builder $query): Builder => $query
                    ->where('status', MaterialStatus::PUBLISHED),
            ])
            ->where('is_active', true)
            ->whereHas('materials', fn (Builder $query): Builder => $query
                ->where('status', MaterialStatus::PUBLISHED))
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.categories.category-library', [
            'categories' => $categories,
        ]);
    }
}
