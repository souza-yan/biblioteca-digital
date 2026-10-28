<?php

namespace App\Livewire\Categories;

use App\Actions\Category\CreateCategory;
use App\Actions\Material\CreateMaterialWithVersion;
use App\Enums\MaterialStatus;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Livewire\Forms\CategoryForm;
use App\Livewire\Forms\MaterialForm;
use App\Models\Category;
use App\Models\Material;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

class CategoryLibrary extends Component
{
    use Interactions;
    use InteractsWithCurrentUser;
    use WithFileUploads;
    use WithPagination;

    public CategoryForm $categoryForm;

    public MaterialForm $materialForm;

    public string $search = '';

    public bool $showCategoryForm = false;

    public bool $showMaterialForm = false;

    public function mount(): void
    {
        Gate::authorize('viewAny', Category::class);
    }

    public function updatedSearch(): void
    {
        Gate::authorize('viewAny', Category::class);
        $this->resetPage();
    }

    public function openCategoryForm(): void
    {
        $this->authorizeStaffCreation(Category::class);
        $this->resetCategoryForm();
        $this->showCategoryForm = true;
    }

    public function closeCategoryForm(): void
    {
        $this->authorizeStaffCreation(Category::class);
        $this->resetCategoryForm();
        $this->showCategoryForm = false;
    }

    public function saveCategory(CreateCategory $createCategory): void
    {
        $actor = $this->authorizeStaffCreation(Category::class);

        $this->categoryForm->categoryId = null;
        $this->categoryForm->generateSlugFromName();

        $createCategory->handle($actor, $this->categoryForm->validate());

        $this->closeCategoryForm();
        $this->resetPage();

        $this->toast()
            ->success('Categoria cadastrada!', 'A categoria foi cadastrada com sucesso.')
            ->send();
    }

    public function openMaterialForm(): void
    {
        $this->authorizeStaffCreation(Material::class);
        $this->resetMaterialForm();
        $this->showMaterialForm = true;
    }

    public function closeMaterialForm(): void
    {
        $this->authorizeStaffCreation(Material::class);
        $this->resetMaterialForm();
        $this->showMaterialForm = false;
    }

    public function saveMaterial(CreateMaterialWithVersion $createMaterialWithVersion): void
    {
        $actor = $this->authorizeStaffCreation(Material::class);
        $temporaryFile = $this->materialForm->file;

        try {
            $this->materialForm->materialId = null;
            if ($temporaryFile instanceof UploadedFile) {
                $this->materialForm->type = strtolower(
                    $temporaryFile->guessExtension() ?? $temporaryFile->getClientOriginalExtension()
                );
            }

            $attributes = $this->materialForm->validate();
            $uploadedFile = $attributes['file'] ?? null;

            if (! $uploadedFile instanceof UploadedFile) {
                $this->addError('materialForm.file', 'O arquivo enviado é inválido.');

                return;
            }

            $changeNote = $attributes['change_note'] ?: null;
            $initialStatus = MaterialStatus::from($attributes['initialStatus']);
            unset($attributes['file'], $attributes['change_note'], $attributes['initialStatus']);

            $createMaterialWithVersion->handle(
                $actor,
                $attributes,
                $uploadedFile,
                $changeNote,
                $initialStatus,
            );

            $this->closeMaterialForm();

            $this->toast()
                ->success('Material criado com sucesso!', 'O material foi cadastrado na Biblioteca Digital.')
                ->send();
        } finally {
            if ($temporaryFile instanceof TemporaryUploadedFile) {
                $temporaryFile->delete();
            }

            $this->materialForm->file = null;
        }
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
            'isStaff' => $this->currentUser()->isStaff(),
            'activeCategories' => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    private function resetCategoryForm(): void
    {
        $this->categoryForm->reset();
        $this->resetValidation();
    }

    private function resetMaterialForm(): void
    {
        $this->materialForm->reset();
        $this->resetValidation();
    }

    /**
     * @param  class-string  $model
     */
    private function authorizeStaffCreation(string $model): User
    {
        $actor = $this->currentUser();
        abort_unless($actor->isStaff(), 403);
        Gate::authorize('create', $model);

        return $actor;
    }
}
