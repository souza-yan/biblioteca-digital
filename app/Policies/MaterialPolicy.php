<?php

namespace App\Policies;

use App\Enums\MaterialStatus;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff() || $user->isTeacher();
    }

    public function browseLibrary(User $user): bool
    {
        return $user->isTeacher();
    }

    public function view(User $user, Material $material): bool
    {
        return $user->isAdmin()
            || $user->isStaff()
            || ($user->isTeacher() && $material->status === MaterialStatus::PUBLISHED);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function update(User $user, Material $material): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function download(User $user, Material $material): bool
    {
        return $user->isAdmin()
            || $user->isStaff()
            || ($user->isTeacher() && $material->status === MaterialStatus::PUBLISHED);
    }

    public function favorite(User $user, Material $material): bool
    {
        return $user->isTeacher() && $this->view($user, $material);
    }

    public function archive(User $user, Material $material): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function publish(User $user, Material $material): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }
}
