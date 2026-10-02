<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;

class MaterialVersionPolicy
{
    public function viewAny(User $user, Material $material): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function create(User $user, Material $material): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }
}
