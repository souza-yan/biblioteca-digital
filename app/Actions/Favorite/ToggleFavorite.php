<?php

namespace App\Actions\Favorite;

use App\Models\Material;
use App\Models\User;

class ToggleFavorite
{
    public function handle(User $user, Material $material): void
    {
        $user->favorites()->toggle($material->getKey());
    }
}
