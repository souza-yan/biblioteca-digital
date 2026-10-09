<?php

namespace App\Livewire\Materials;

use App\Actions\Material\ArchiveMaterial;
use App\Actions\Material\CreateMaterialWithVersion;
use App\Actions\Material\PublishMaterial;
use App\Actions\Material\UpdateMaterial;
use App\Enums\MaterialStatus;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Livewire\Forms\MaterialForm;
use App\Models\Category;
use App\Models\Material;
use App\Models\User;
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

class MaterialManager extends Component
{
    use InteractsWithCurrentUser;
    use WithFileUploads;
    use WithPagination;

    public MaterialForm $form;

    public ?string $search = '';

    public string $statusFilter = '';

    public ?string $categoryFilter = '';

    public bool $showForm = false;

    #[Locked]
    public ?int $editingMaterialId = null;

    public function mount(): void
    {
        $this->authorizeManager();

        $this->statusFilter = MaterialStatus::PUBLISHED->value;
    }

    public function updatedSearch(): void
    {
        $this->authorizeManager();
        $this->resetPage();
    }

    public function updatedStatusFilter($value): void
    {
        $this->authorizeManager();

        if (MaterialStatus::tryFrom((string) $value) === null) {
            $this->statusFilter = MaterialStatus::PUBLISHED->value;
        }

        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->authorizeManager();
        $this->resetPage();
    }

    public function openCreate(): void
    {
        Gate::authorize('create', Material::class);
        $this->resetForm();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->authorizeManager();
        $this->resetForm();
        $this->showForm = false;
    }

    public function editMaterial(int $materialId): void
    {
        $material = $this->findManagedMaterial($materialId, 'update');
        $this->resetValidation();

        $this->editingMaterialId = $material->getKey();
        $this->form->materialId = $material->getKey();
        $this->form->title = $material->title;
        $this->form->description = $material->description ?? '';
        $this->form->category_id = (string) $material->category_id;
        $this->form->type = $material->type;
        $this->form->author = $material->author;
        $this->showForm = true;
    }

    public function save(CreateMaterialWithVersion $createMaterialWithVersion, UpdateMaterial $updateMaterial): void
    {
        $actor = $this->authorizeManager();
        $material = $this->editingMaterialId === null ? null : $this->findManagedMaterial($this->editingMaterialId, 'update');
        $temporaryFile = $this->form->file;

        if ($material === null) {
            Gate::authorize('create', Material::class);
        }

        try {
            $this->form->materialId = $material?->getKey();
            $attributes = $this->form->validate();

            if ($material === null) {
                $uploadedFile = $attributes['file'] ?? null;

                if (!$uploadedFile instanceof UploadedFile) {
                    $this->addError('form.file', 'O arquivo enviado é inválido.');

                    return;
                }

                $initialStatus = MaterialStatus::from($attributes['initialStatus']);
                $changeNote = $attributes['change_note'] ?: null;
                unset($attributes['file'], $attributes['change_note'], $attributes['initialStatus']);

                $createMaterialWithVersion->handle($actor, $attributes, $uploadedFile, $changeNote, $initialStatus);
            } else {
                $updateMaterial->handle($actor, $material, $attributes);
            }

            $this->resetForm();
            $this->showForm = false;
            $this->resetPage();
        } finally {
            if ($temporaryFile instanceof TemporaryUploadedFile) {
                $temporaryFile->delete();
            }

            $this->form->file = null;
        }
    }

    public function publish(int $materialId, PublishMaterial $publishMaterial): void
    {
        $actor = $this->authorizeManager();
        $material = $this->findManagedMaterial($materialId, 'publish');

        if (!$material->currentVersion()->exists()) {
            $this->addError('publish', 'O material precisa ter uma versão atual para ser publicado.');

            return;
        }

        $publishMaterial->handle($actor, $material);
        $this->resetPage();
    }

    public function archive(int $materialId, ArchiveMaterial $archiveMaterial): void
    {
        $actor = $this->authorizeManager();
        $material = $this->findManagedMaterial($materialId, 'archive');

        $archiveMaterial->handle($actor, $material);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $this->authorizeManager();

        $status = MaterialStatus::tryFrom($this->statusFilter) ?? MaterialStatus::PUBLISHED;

        $materials = Material::query()
            ->with(['category', 'currentVersion'])
            ->when(filled($this->search), function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('title', 'like', '%' . $this->search . '%')->orWhere('author', 'like', '%' . $this->search . '%');
                });
            })
            ->where('status', $status->value)
            ->when(filled($this->categoryFilter), fn(Builder $query): Builder => $query->where('category_id', $this->categoryFilter))
            ->orderBy('title')
            ->paginate(15);

        foreach ($materials as $material) {
            Gate::authorize('update', $material);
        }

        return view('livewire.materials.material-manager', [
            'materials' => $materials,
            'categories' => Category::query()->orderBy('name')->get(),
            'activeCategories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'statusOptions' => [
                ...array_map(
                    fn(MaterialStatus $status): array => [
                        'label' => $status->label(),
                        'value' => $status->value,
                    ],
                    MaterialStatus::cases(),
                ),
            ],
            'creationStatuses' => [MaterialStatus::DRAFT, MaterialStatus::PUBLISHED],
        ]);
    }

    private function findManagedMaterial(int $materialId, string $ability): Material
    {
        $material = Material::query()->findOrFail($materialId);

        Gate::authorize($ability, $material);

        return $material;
    }

    private function authorizeManager(): User
    {
        $actor = $this->currentUser();

        Gate::authorize('create', Material::class);

        return $actor;
    }

    private function resetForm(): void
    {
        $this->form->reset();
        $this->editingMaterialId = null;
        $this->resetValidation();
    }
}
