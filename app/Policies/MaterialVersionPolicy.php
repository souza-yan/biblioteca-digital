<?php

namespace App\Policies;

use App\Enums\MaterialStatus;
use App\Models\Material;
use App\Models\User;

class MaterialVersionPolicy
{
    public function viewAny(User $user, Material $material): bool
    {
        return $user->is_active && ($user->isAdmin()
            || ($user->isStaff() && (
                $material->status === MaterialStatus::PUBLISHED
                || ($material->status === MaterialStatus::DRAFT && $material->created_by === $user->getKey())
            )));
    }

    public function create(User $user, Material $material): bool
    {
        return $user->is_active && ($user->isAdmin()
            || ($user->isStaff()
                && $user->hasExactlyOneActiveSchool()
                && $material->status === MaterialStatus::DRAFT
                && $material->created_by === $user->getKey()));
    }
}
