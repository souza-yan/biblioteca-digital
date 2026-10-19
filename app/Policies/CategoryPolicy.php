<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && ($user->isAdmin() || $user->isStaff() || $user->isTeacher());
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->isAdmin();
    }

    public function update(User $user, Category $category): bool
    {
        return $user->is_active && $user->isAdmin();
    }

    public function toggleActive(User $user, Category $category): bool
    {
        return $user->is_active && $user->isAdmin();
    }
}
