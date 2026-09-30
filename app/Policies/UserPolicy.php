<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function manage(User $actor, User $target): bool
    {
        // Admin mexe em qualquer um
        if ($actor->isAdmin()) {
            return true;
        }

        // Gestão só mexe em professores
        return $actor->isStaff() && $target->isTeacher();
    }
}
