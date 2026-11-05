<?php

namespace App\Livewire\Materials;

use App\Actions\Activity\LogActivity;
use App\Actions\Favorite\ToggleFavorite;
use App\Actions\MaterialVersion\CreateMaterialVersion;
use App\Enums\ActivityAction;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Livewire\Forms\MaterialVersionForm;
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

    public MaterialVersionForm $versionForm;

    #[Locked]
    public int $materialId;

    public function mount(Material $material, LogActivity $logActivity): void
    {
        $this->materialId = $material->getKey();
        $viewableMaterial = $this->findViewableMaterial();
        $actor = $this->currentUser();

        if ($actor->isTeacher()) {
            $logActivity->handle(
                $actor,
                ActivityAction::MATERIAL_PREVIEWED,
                'Material consultado.',
                $viewableMaterial,
            );
        }
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
        $canManageVersions = Gate::allows('create', [MaterialVersion::class, $material]);
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
            'previewUrl' => $actor->isTeacher()
                ? route('previews.show', ['material' => $material, 'access_recorded' => 1])
                : route('previews.show', $material),
            'isFavorited' => $isFavorited,
            'canManageVersions' => $canManageVersions,
            'canViewVersionHistory' => $canViewVersionHistory,
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
