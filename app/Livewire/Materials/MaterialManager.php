<?php

namespace App\Livewire\Materials;

use App\Actions\Material\ArchiveMaterial;
use App\Actions\Material\CreateMaterial;
use App\Actions\Material\PublishMaterial;
use App\Actions\Material\UpdateMaterial;
use App\Enums\MaterialStatus;
use App\Livewire\Forms\MaterialForm;
use App\Models\Category;
use App\Models\Material;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class MaterialManager extends Component
{
    use WithPagination;

    public MaterialForm $form;

    public string $search = '';

    public string $statusFilter = '';

    public string $categoryFilter = '';

    public bool $showForm = false;

    public ?int $editingMaterialId = null;

    public function mount(): void
    {
        $this->authorizeManager();
    }

    public function updatedSearch(): void
    {
        $this->authorizeManager();
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->authorizeManager();
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

    public function save(CreateMaterial $createMaterial, UpdateMaterial $updateMaterial): void
    {
        $actor = $this->authorizeManager();
        $material = $this->editingMaterialId === null
            ? null
            : $this->findManagedMaterial($this->editingMaterialId, 'update');

        if ($material === null) {
            Gate::authorize('create', Material::class);
        }

        $this->form->materialId = $material?->getKey();
        $attributes = $this->form->validate();

        if ($material === null) {
            $createMaterial->handle($actor, $attributes);
        } else {
            $updateMaterial->handle($material, $attributes);
        }

        $this->resetForm();
        $this->showForm = false;
        $this->resetPage();
    }

    public function publish(int $materialId, PublishMaterial $publishMaterial): void
    {
        $material = $this->findManagedMaterial($materialId, 'publish');

        if (! $material->currentVersion()->exists()) {
            $this->addError('publish', 'O material precisa ter uma versão atual para ser publicado.');

            return;
        }

        $publishMaterial->handle($material);
        $this->resetPage();
    }

    public function archive(int $materialId, ArchiveMaterial $archiveMaterial): void
    {
        $material = $this->findManagedMaterial($materialId, 'archive');

        $archiveMaterial->handle($material);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $this->authorizeManager();

        $materials = Material::query()
            ->with(['category', 'currentVersion'])
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('author', 'like', '%'.$this->search.'%');
                });
            })
            ->when(MaterialStatus::tryFrom($this->statusFilter), fn (Builder $query, MaterialStatus $status): Builder => $query->where('status', $status->value))
            ->when($this->categoryFilter !== '', fn (Builder $query): Builder => $query->where('category_id', $this->categoryFilter))
            ->orderBy('title')
            ->paginate(15);

        foreach ($materials as $material) {
            Gate::authorize('update', $material);
        }

        return view('livewire.materials.material-manager', [
            'materials' => $materials,
            'categories' => Category::query()->orderBy('name')->get(),
            'activeCategories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => MaterialStatus::cases(),
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
        $actor = Auth::user();

        abort_unless($actor instanceof User, 403);

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
