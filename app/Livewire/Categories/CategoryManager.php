<?php

namespace App\Livewire\Categories;

use App\Actions\Category\CreateCategory;
use App\Actions\Category\ToggleCategoryActive;
use App\Actions\Category\UpdateCategory;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Livewire\Forms\CategoryForm;
use App\Models\Category;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryManager extends Component
{
    use InteractsWithCurrentUser;
    use WithPagination;

    public CategoryForm $form;

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingCategoryId = null;

    public function mount(): void
    {
        $this->authorizeManager();
    }

    public function updatedSearch(): void
    {
        $this->authorizeManager();
        $this->resetPage();
    }

    public function openCreate(): void
    {
        Gate::authorize('create', Category::class);
        $this->resetForm();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->authorizeManager();
        $this->resetForm();
        $this->showForm = false;
    }

    public function editCategory(int $categoryId): void
    {
        $category = $this->findManagedCategory($categoryId, 'update');
        $this->resetValidation();

        $this->editingCategoryId = $category->getKey();
        $this->form->categoryId = $category->getKey();
        $this->form->name = $category->name;
        $this->form->slug = $category->slug;
        $this->form->description = $category->description ?? '';
        $this->form->is_active = $category->is_active;
        $this->showForm = true;
    }

    public function save(CreateCategory $createCategory, UpdateCategory $updateCategory): void
    {
        $actor = $this->authorizeManager();
        $category = $this->editingCategoryId === null
            ? null
            : $this->findManagedCategory($this->editingCategoryId, 'update');

        if ($category === null) {
            Gate::authorize('create', Category::class);
        }

        $this->form->categoryId = $category?->getKey();
        $this->form->generateSlugFromName();
        $attributes = $this->form->validate();

        if ($category === null) {
            $createCategory->handle($actor, $attributes);
        } else {
            $updateCategory->handle($actor, $category, $attributes);
        }

        $this->resetForm();
        $this->showForm = false;
        $this->resetPage();
    }

    public function toggleActive(int $categoryId, ToggleCategoryActive $toggleCategoryActive): void
    {
        $actor = $this->authorizeManager();
        $category = $this->findManagedCategory($categoryId, 'toggleActive');

        $toggleCategoryActive->handle($actor, $category);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $this->authorizeManager();

        $categories = Category::query()
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(15);

        foreach ($categories as $category) {
            Gate::authorize('update', $category);
        }

        return view('livewire.categories.category-manager', [
            'categories' => $categories,
        ]);
    }

    private function findManagedCategory(int $categoryId, string $ability): Category
    {
        $category = Category::query()->findOrFail($categoryId);

        Gate::authorize($ability, $category);

        return $category;
    }

    private function authorizeManager(): User
    {
        Gate::authorize('viewAny', Category::class);
        Gate::authorize('create', Category::class);

        $actor = $this->currentUser();

        return $actor;
    }

    private function resetForm(): void
    {
        $this->form->reset();
        $this->editingCategoryId = null;
        $this->resetValidation();
    }
}
