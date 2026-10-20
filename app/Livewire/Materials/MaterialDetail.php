<?php

namespace App\Livewire\Materials;

use App\Actions\Favorite\ToggleFavorite;
use App\Actions\Material\ArchiveMaterial;
use App\Actions\Material\PublishMaterial;
use App\Actions\Material\UpdateMaterial;
use App\Actions\MaterialVersion\CreateMaterialVersion;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Livewire\Forms\MaterialForm;
use App\Livewire\Forms\MaterialVersionForm;
use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use TallStackUi\Traits\Interactions;

class MaterialDetail extends Component
{
    use Interactions;
    use InteractsWithCurrentUser;
    use WithFileUploads;

    public MaterialForm $materialForm;

    public MaterialVersionForm $versionForm;

    public bool $showEditForm = false;

    #[Locked]
    public int $materialId;

    public function mount(Material $material): void
    {
        $this->materialId = $material->getKey();
        $this->findViewableMaterial();
    }

    public function publish(PublishMaterial $publishMaterial): void
    {
        $actor = $this->currentUser();

        $material = $this->findViewableMaterial();
        Gate::authorize('publish', $material);

        if (! $material->currentVersion()->exists()) {
            $this->addError('publish', 'O material precisa ter uma versão atual para ser publicado.');

            return;
        }

        $publishMaterial->handle($actor, $material);

        $this->toast()
            ->success('Material publicado!', 'O material já está disponível na biblioteca.')
            ->send();
    }

    public function archive(ArchiveMaterial $archiveMaterial): void
    {
        $actor = $this->currentUser();

        $material = $this->findViewableMaterial();
        Gate::authorize('archive', $material);

        $archiveMaterial->handle($actor, $material);

        $this->toast()
            ->success('Material arquivado!', 'O material foi arquivado com sucesso.')
            ->send();
    }

    public function openEditForm(): void
    {
        $material = $this->findViewableMaterial();
        Gate::authorize('update', $material);

        $this->materialForm->reset();
        $this->materialForm->materialId = $material->getKey();
        $this->materialForm->title = $material->title;
        $this->materialForm->description = $material->description ?? '';
        $this->materialForm->category_id = (string) $material->category_id;
        $this->materialForm->type = $material->type;
        $this->materialForm->author = $material->author;
        $this->resetValidation();
        $this->showEditForm = true;
    }

    public function closeEditForm(): void
    {
        $material = $this->findViewableMaterial();
        Gate::authorize('update', $material);

        $this->materialForm->reset();
        $this->resetValidation();
        $this->showEditForm = false;
    }

    public function saveMaterial(UpdateMaterial $updateMaterial): void
    {
        $actor = $this->currentUser();
        $material = $this->findViewableMaterial();
        Gate::authorize('update', $material);

        $this->materialForm->materialId = $material->getKey();
        $this->materialForm->type = $material->type;
        $attributes = $this->materialForm->validate();

        $updateMaterial->handle($actor, $material, $attributes);

        $this->materialForm->reset();
        $this->resetValidation();
        $this->showEditForm = false;

        $this->toast()
            ->success('Material atualizado!', 'As alterações foram salvas corretamente.')
            ->send();
    }

    public function toggleFavorite(ToggleFavorite $toggleFavorite): void
    {
        $material = Material::query()->findOrFail($this->materialId);
        Gate::authorize('favorite', $material);

        $actor = $this->currentUser();

        $toggleFavorite->handle($actor, $material);

        $isFavorited = $actor->favorites()->whereKey($material->getKey())->exists();

        $this->toast()
            ->success(
                $isFavorited ? 'Material favoritado!' : 'Material removido dos favoritos!',
                $isFavorited
                    ? 'O material foi adicionado aos seus favoritos.'
                    : 'O material foi removido dos seus favoritos.'
            )
            ->send();
    }

    public function saveVersion(CreateMaterialVersion $createMaterialVersion): void
    {
        $actor = $this->currentUser();

        $material = $this->findViewableMaterial();
        Gate::authorize('create', [MaterialVersion::class, $material]);

        $temporaryFile = $this->versionForm->file;

        try {
            $attributes = $this->versionForm->validate();
            $uploadedFile = $attributes['file'] ?? null;

            if (! $uploadedFile instanceof UploadedFile) {
                $this->addError('versionForm.file', 'O arquivo enviado é inválido.');

                return;
            }

            $createMaterialVersion->handle(
                $material,
                $actor,
                $uploadedFile,
                $attributes['change_note'] ?: null,
            );

            $this->toast()
                ->success('Nova versão cadastrada!', 'O arquivo foi adicionado ao histórico do material.')
                ->send();

            $this->versionForm->reset();
            $this->resetValidation();
        } finally {
            if ($temporaryFile instanceof TemporaryUploadedFile) {
                $temporaryFile->delete();
            }

            $this->versionForm->file = null;
        }
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $material = $this->findViewableMaterial();
        $actor = $this->currentUser();
        $canManageVersions = $actor->isAdmin();
        $canEditMaterial = Gate::allows('update', $material);
        $canViewVersionHistory = $actor->isAdmin() || $actor->isStaff();
        $isFavorited = $actor->isTeacher()
            && $actor->favorites()->whereKey($material->getKey())->exists();
        $versions = collect();

        if ($canViewVersionHistory) {
            Gate::authorize('viewAny', [MaterialVersion::class, $material]);
            $versions = $material->versions()
                ->with('publisher')
                ->orderByDesc('version_number')
                ->get();
        }

        return view('livewire.materials.material-detail', [
            'material' => $material,
            'isTeacher' => $actor->isTeacher(),
            'isFavorited' => $isFavorited,
            'canManageVersions' => $canManageVersions,
            'canEditMaterial' => $canEditMaterial,
            'canViewVersionHistory' => $canViewVersionHistory,
            'activeCategories' => $canEditMaterial
                ? Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'versions' => $versions,
        ]);
    }

    private function findViewableMaterial(): Material
    {
        $material = Material::query()
            ->with(['category', 'creator', 'currentVersion.publisher'])
            ->findOrFail($this->materialId);

        Gate::authorize('view', $material);

        return $material;
    }
}
