<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff() || $user->isTeacher();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function update(User $user, Category $category): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function toggleActive(User $user, Category $category): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }
}
