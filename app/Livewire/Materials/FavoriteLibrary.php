<?php

namespace App\Livewire\Materials;

use App\Enums\MaterialStatus;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class FavoriteLibrary extends Component
{
    use InteractsWithCurrentUser;
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('browseLibrary', Material::class);
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
