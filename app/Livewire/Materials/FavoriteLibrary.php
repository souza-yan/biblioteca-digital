<?php

namespace App\Livewire\Materials;

use App\Enums\MaterialStatus;
use App\Models\Material;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class FavoriteLibrary extends Component
{
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('browseLibrary', Material::class);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        Gate::authorize('browseLibrary', Material::class);

        $user = Auth::user();
        abort_unless($user instanceof User, 403);

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
