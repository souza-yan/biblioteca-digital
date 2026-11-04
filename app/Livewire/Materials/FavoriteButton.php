<?php

namespace App\Livewire\Materials;

use App\Actions\Favorite\ToggleFavorite;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class FavoriteButton extends Component
{
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
    }

    public function render(): View
    {
        return view('livewire.materials.favorite-button');
    }
}
