<?php

namespace App\Livewire\Materials;

use App\Actions\Activity\LogActivity;
use App\Actions\Favorite\ToggleFavorite;
use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

class FavoriteLibrary extends Component
{
    use Interactions;
    use InteractsWithCurrentUser;
    use WithPagination;

    public bool $showMobileMaterialDetail = false;

    public ?int $mobileMaterialId = null;

    public function mount(): void
    {
        Gate::authorize('browseLibrary', Material::class);
    }

    public function removeFavorite(int $materialId, ToggleFavorite $toggleFavorite): void
    {
        $material = Material::query()->findOrFail($materialId);
        Gate::authorize('favorite', $material);

        $actor = $this->currentUser();

        abort_unless($actor->favorites()->whereKey($material->getKey())->exists(), 404);

        $toggleFavorite->handle($actor, $material);
        $this->resetPage();

        $this->toast()
            ->success('Material removido dos favoritos!', 'O material não aparecerá mais nesta lista.')
            ->send();
    }

    public function openMobileMaterialDetail(int $materialId, LogActivity $logActivity): void
    {
        $material = $this->currentUser()
            ->favorites()
            ->with(['category', 'currentVersion'])
            ->where('materials.status', MaterialStatus::PUBLISHED)
            ->findOrFail($materialId);

        Gate::authorize('view', $material);

        $logActivity->handle(
            $this->currentUser(),
            ActivityAction::MATERIAL_PREVIEWED,
            'Material consultado.',
            $material,
        );

        $this->mobileMaterialId = $material->getKey();
        $this->showMobileMaterialDetail = true;
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        Gate::authorize('browseLibrary', Material::class);

        $user = $this->currentUser();

        $materials = $user->favorites()
            ->with(['category', 'currentVersion'])
            ->where('materials.status', MaterialStatus::PUBLISHED)
            ->orderBy('materials.title')
            ->paginate(15);
        $mobileMaterial = $this->showMobileMaterialDetail && $this->mobileMaterialId !== null
            ? $user->favorites()
                ->with(['category', 'currentVersion'])
                ->where('materials.status', MaterialStatus::PUBLISHED)
                ->findOrFail($this->mobileMaterialId)
            : null;

        if ($mobileMaterial instanceof Material) {
            Gate::authorize('view', $mobileMaterial);
        }

        return view('livewire.materials.favorite-library', [
            'materials' => $materials,
            'mobileMaterial' => $mobileMaterial,
            'mobilePreviewUrl' => $mobileMaterial instanceof Material
                ? route('previews.show', ['material' => $mobileMaterial, 'access_recorded' => 1])
                : null,
        ]);
    }
}
