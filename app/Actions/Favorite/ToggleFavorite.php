<?php

namespace App\Actions\Favorite;

use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ToggleFavorite
{
    public function handle(User $user, Material $material): void
    {
        DB::transaction(function () use ($user, $material): void {
            $currentUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $currentMaterial = Material::query()->lockForUpdate()->findOrFail($material->getKey());
            Gate::forUser($currentUser)->authorize('favorite', $currentMaterial);

            $currentUser->favorites()->toggle($currentMaterial->getKey());
        });
    }
}
