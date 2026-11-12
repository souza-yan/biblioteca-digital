<?php

namespace App\Livewire\Categories;

use App\Actions\Category\CreateCategory;
use App\Actions\Category\ListCategoryOptions;
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
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    public ?int $preferredMaterialCategoryId = null;

    public function mount(): void
    {
        Gate::authorize('viewAny', Category::class);
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

    public function saveCategory(CreateCategory $createCategory): void
    {
        $actor = $this->currentUser();
        Gate::authorize('create', Category::class);

        $this->categoryForm->generateSlugFromName();
        $this->categoryForm->categoryId = null;
        $attributes = $this->categoryForm->validate();
        $category = $createCategory->handle($actor, $attributes);

        $this->toast()
            ->success('Categoria cadastrada!', 'A categoria foi cadastrada com sucesso.')
            ->send();

        $this->resetCategoryForm();
        $this->showCategoryForm = false;
        $this->preferredMaterialCategoryId = $category->getKey();
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
        $retainUpload = false;

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
        } catch (ValidationException $exception) {
            $retainUpload = $temporaryFile instanceof TemporaryUploadedFile
                && ! array_key_exists('materialForm.file', $exception->errors());

            throw $exception;
        } finally {
            if (! $retainUpload) {
                if ($temporaryFile instanceof TemporaryUploadedFile) {
                    $temporaryFile->delete();
                }

                $this->materialForm->file = null;
            }
        }
    }

    #[Layout('layouts.app')]
    public function render(ListCategoryOptions $listCategoryOptions): View
    {
        Gate::authorize('viewAny', Category::class);
        $actor = $this->currentUser();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount([
                'materials as published_materials_count' => fn (Builder $query): Builder => $query
                    ->where('status', MaterialStatus::PUBLISHED),
            ])
            ->when(! $actor->isStaff(), fn (Builder $query): Builder => $query
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

        $uncategorizedCount = trim($this->search) === '' || Str::contains(Str::lower('Sem categoria'), Str::lower(trim($this->search)))
            ? Material::query()->published()->whereNull('category_id')->count()
            : 0;
        $categoryCards = $categories->getCollection()->values();

        if ($uncategorizedCount > 0) {
            $uncategorizedCategory = new Category([
                'name' => 'Sem categoria',
                'description' => 'Materiais publicados sem uma categoria ativa.',
            ]);
            $uncategorizedCategory->setAttribute('published_materials_count', $uncategorizedCount);
            $categoryCards = $categoryCards->prepend($uncategorizedCategory);
        }

        return view('livewire.categories.category-library', [
            'categories' => $categories,
            'categoryCards' => $categoryCards,
            'uncategorizedCount' => $uncategorizedCount,
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
        $this->resetValidation();
    }

    private function resetMaterialForm(): void
    {
        $this->materialForm->reset();
        $this->resetValidation();
    }
}
