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
        return $user->is_active && ($user->isAdmin()
            || ($user->isStaff() && $user->hasExactlyOneActiveSchool()));
    }

    public function update(User $user, Category $category): bool
    {
        return $this->canManage($user, $category);
    }

    public function toggleActive(User $user, Category $category): bool
    {
        return $this->canManage($user, $category);
    }

    private function canManage(User $user, Category $category): bool
    {
        return $user->is_active && ($user->isAdmin()
            || ($user->isStaff()
                && $user->hasExactlyOneActiveSchool()
                && $category->created_by === $user->getKey()));
    }
}
