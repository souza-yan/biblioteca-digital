<?php

namespace App\Livewire\Categories;

use App\Actions\Category\CreateCategory;
use App\Actions\Category\ListCategoryOptions;
use App\Actions\Category\ToggleCategoryActive;
use App\Actions\Category\UpdateCategory;
use App\Actions\Material\CreateMaterialWithVersion;
use App\Enums\MaterialStatus;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Livewire\Forms\CategoryForm;
use App\Livewire\Forms\MaterialForm;
use App\Models\Category;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
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

    #[Locked]
    public ?int $editingCategoryId = null;

    public bool $showMaterialForm = false;

    public ?int $preferredMaterialCategoryId = null;

    public function mount(): void
    {
        Gate::authorize('viewAny', Category::class);
    }

    public function placeholder(): View
    {
        return view('livewire.categories.category-library-placeholder');
    }

    public function updatedSearch(): void
    {
        Gate::authorize('viewAny', Category::class);
        $this->resetPage();
    }

    public function openCategoryCreate(): void
    {
        Gate::authorize('create', Category::class);

        $this->resetCategoryForm();
        $this->showCategoryForm = true;
    }

    public function closeCategoryForm(): void
    {
        $this->resetCategoryForm();
        $this->showCategoryForm = false;
    }

    public function editCategory(int $categoryId): void
    {
        $category = $this->findManageableCategory($categoryId, 'update');

        $this->resetValidation();
        $this->editingCategoryId = $category->getKey();
        $this->categoryForm->categoryId = $category->getKey();
        $this->categoryForm->name = $category->name;
        $this->categoryForm->slug = $category->slug;
        $this->categoryForm->description = $category->description ?? '';
        $this->showCategoryForm = true;
    }

    public function saveCategory(
        CreateCategory $createCategory,
        UpdateCategory $updateCategory,
    ): void {
        $actor = $this->currentUser();

        $category = $this->editingCategoryId === null
            ? null
            : $this->findManageableCategory($this->editingCategoryId, 'update');

        if ($category === null) {
            Gate::authorize('create', Category::class);
        }

        $this->categoryForm->generateSlugFromName();
        $this->categoryForm->categoryId = $category?->getKey();
        $attributes = $this->categoryForm->validate();

        if ($category === null) {
            $category = $createCategory->handle($actor, $attributes);
            $message = 'A categoria foi cadastrada com sucesso.';
        } else {
            $updateCategory->handle($actor, $category, $attributes);
            $message = 'As informações da categoria foram atualizadas com sucesso.';
        }

        $this->toast()
            ->success('Categoria atualizada!', $message)
            ->send();

        $this->resetCategoryForm();
        $this->showCategoryForm = false;
        $this->preferredMaterialCategoryId = $category->getKey();
    }

    public function toggleCategoryActive(
        int $categoryId,
        ToggleCategoryActive $toggleCategoryActive,
    ): void {
        $actor = $this->currentUser();
        $category = $this->findManageableCategory($categoryId, 'toggleActive');

        $toggleCategoryActive->handle($actor, $category);
        $category->refresh();

        $this->toast()
            ->success(
                'Status atualizado!',
                'A categoria foi '.($category->is_active ? 'ativada' : 'desativada').' com sucesso.',
            )
            ->send();
    }

    public function openMaterialCreate(): void
    {
        Gate::authorize('create', Material::class);

        $this->resetMaterialForm();
        $this->materialForm->category_id = (string) $this->preferredMaterialCategoryId;
        $this->preferredMaterialCategoryId = null;
        $this->showMaterialForm = true;
    }

    public function closeMaterialForm(): void
    {
        $this->resetMaterialForm();
        $this->showMaterialForm = false;
    }

    public function saveMaterial(CreateMaterialWithVersion $createMaterialWithVersion): void
    {
        $actor = $this->currentUser();
        Gate::authorize('create', Material::class);

        $temporaryFile = $this->materialForm->file;

        try {
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

            $initialStatus = MaterialStatus::from($attributes['initialStatus']);
            $changeNote = $attributes['change_note'] ?: null;

            unset($attributes['file'], $attributes['change_note'], $attributes['initialStatus']);

            $createMaterialWithVersion->handle(
                $actor,
                $attributes,
                $uploadedFile,
                $changeNote,
                $initialStatus,
            );

            $this->toast()
                ->success('Material criado com sucesso!', 'O material foi cadastrado na Biblioteca Digital.')
                ->send();

            $this->resetMaterialForm();
            $this->showMaterialForm = false;
        } finally {
            if ($temporaryFile instanceof TemporaryUploadedFile) {
                $temporaryFile->delete();
            }

            $this->materialForm->file = null;
        }
    }

    #[Layout('layouts.app', ['usesElementSkeletons' => true])]
    public function render(ListCategoryOptions $listCategoryOptions): View
    {
        Gate::authorize('viewAny', Category::class);
        $actor = $this->currentUser();

        $categories = Category::query()
            ->withCount([
                'materials as published_materials_count' => fn (Builder $query): Builder => $query
                    ->where('status', MaterialStatus::PUBLISHED),
            ])
            ->when(! $actor->isStaff(), fn (Builder $query): Builder => $query
                ->where('is_active', true)
                ->whereHas('materials', fn (Builder $query): Builder => $query
                    ->where('status', MaterialStatus::PUBLISHED)))
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
            'activeCategories' => $listCategoryOptions->active(),
            'creationStatuses' => [
                MaterialStatus::DRAFT,
                MaterialStatus::PUBLISHED,
            ],
        ]);
    }

    private function resetCategoryForm(): void
    {
        $this->categoryForm->reset();
        $this->editingCategoryId = null;
        $this->resetValidation();
    }

    private function findManageableCategory(int $categoryId, string $ability): Category
    {
        $category = Category::query()->findOrFail($categoryId);

        Gate::authorize($ability, $category);

        return $category;
    }

    private function resetMaterialForm(): void
    {
        $this->materialForm->reset();
        $this->resetValidation();
    }
}
