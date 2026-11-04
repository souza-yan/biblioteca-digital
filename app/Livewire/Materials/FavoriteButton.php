<?php

namespace App\Livewire\Materials;

use App\Actions\Favorite\ToggleFavorite;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class FavoriteButton extends Component
{
    use Interactions;
    use InteractsWithCurrentUser;

    public int $materialId;

    public bool $isFavorited = false;

    public function mount(Material $material, bool $isFavorited = false): void
    {
        Gate::authorize('favorite', $material);

        $this->materialId = $material->getKey();
        $this->isFavorited = $isFavorited;
    }

    public function toggle(ToggleFavorite $toggleFavorite): void
    {
        $material = Material::query()->findOrFail($this->materialId);
        Gate::authorize('favorite', $material);

        $actor = $this->currentUser();
        $toggleFavorite->handle($actor, $material);
        $this->isFavorited = $actor->favorites()->whereKey($material->getKey())->exists();

        $this->toast()
            ->success(
                $this->isFavorited ? 'Material favoritado!' : 'Material removido dos favoritos!',
                $this->isFavorited
                    ? 'O material foi adicionado aos seus favoritos.'
                    : 'O material foi removido dos seus favoritos.'
            )
            ->send();
    }

    public function render(): View
    {
        return view('livewire.materials.favorite-button');
    }
}
