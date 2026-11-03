<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;

class MaterialVersionPolicy
{
    public function viewAny(User $user, Material $material): bool
    {
        return $this->isGlobalManager($user);
    }

    public function create(User $user, Material $material): bool
    {
        return $user->is_active && ($user->isAdmin()
            || ($user->isStaff()
                && $user->hasExactlyOneActiveSchool()
                && $material->created_by === $user->getKey()));
    }

    private function isGlobalManager(User $user): bool
    {
        return $user->is_active && ($user->isAdmin()
            || ($user->isStaff() && $user->hasExactlyOneActiveSchool()));
    }
}
