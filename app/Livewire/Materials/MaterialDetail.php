<?php

namespace App\Livewire\Materials;

use App\Actions\Favorite\ToggleFavorite;
use App\Actions\Material\ArchiveMaterial;
use App\Actions\Material\PublishMaterial;
use App\Actions\MaterialVersion\CreateMaterialVersion;
use App\Livewire\Forms\MaterialVersionForm;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class MaterialDetail extends Component
{
    use WithFileUploads;

    public MaterialVersionForm $versionForm;

    #[Locked]
    public int $materialId;

    public function mount(Material $material): void
    {
        $this->materialId = $material->getKey();
        $this->findViewableMaterial();
    }

    public function publish(PublishMaterial $publishMaterial): void
    {
        $material = $this->findViewableMaterial();
        Gate::authorize('publish', $material);

        if (! $material->currentVersion()->exists()) {
            $this->addError('publish', 'O material precisa ter uma versão atual para ser publicado.');

            return;
        }

        $publishMaterial->handle($material);
    }

    public function archive(ArchiveMaterial $archiveMaterial): void
    {
        $material = $this->findViewableMaterial();
        Gate::authorize('archive', $material);

        $archiveMaterial->handle($material);
    }

    public function toggleFavorite(ToggleFavorite $toggleFavorite): void
    {
        $material = Material::query()->findOrFail($this->materialId);
        Gate::authorize('favorite', $material);

        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);

        $toggleFavorite->handle($actor, $material);
    }

    public function saveVersion(CreateMaterialVersion $createMaterialVersion): void
    {
        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);

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
        $actor = Auth::user();
        $canManageVersions = $actor instanceof User && ($actor->isAdmin() || $actor->isStaff());
        $isFavorited = $actor instanceof User
            && $actor->isTeacher()
            && $actor->favorites()->whereKey($material->getKey())->exists();
        $versions = collect();

        if ($canManageVersions) {
            Gate::authorize('viewAny', [MaterialVersion::class, $material]);
            $versions = $material->versions()
                ->with('publisher')
                ->orderByDesc('version_number')
                ->get();
        }

        return view('livewire.materials.material-detail', [
            'material' => $material,
            'isTeacher' => $actor instanceof User && $actor->isTeacher(),
            'isFavorited' => $isFavorited,
            'canManageVersions' => $canManageVersions,
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
