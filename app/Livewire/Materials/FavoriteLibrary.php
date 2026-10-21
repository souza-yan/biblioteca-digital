<?php

namespace App\Livewire\Materials;

use App\Actions\Favorite\ToggleFavorite;
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

        return view('livewire.materials.favorite-library', [
            'materials' => $materials,
        ]);
    }
}
