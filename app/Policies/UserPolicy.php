<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function create(User $actor): bool
    {
        return $actor->is_active && ($actor->isAdmin()
            || ($actor->isStaff() && $actor->hasExactlyOneActiveSchool()));
    }

    public function changeRole(User $actor, User $target): bool
    {
        return $actor->is_active && $actor->isAdmin() && ! $actor->is($target);
    }

    public function resetPassword(User $actor, User $target): bool
    {
        return $actor->is_active && $actor->isAdmin();
    }

    public function resetOwnPasswordToDefault(User $actor, User $target): bool
    {
        return $actor->is_active && $actor->is($target);
    }

    public function manage(User $actor, User $target): bool
    {
        // Admin mexe em qualquer um
        if (! $actor->is_active) {
            return false;
        }

        if ($actor->isAdmin()) {
            return true;
        }

        // Gestão só mexe em professores
        if (! $actor->isStaff() || ! $target->isTeacher()) {
            return false;
        }

        $school = $actor->school();

        return $school !== null && $target->hasSchoolMembership($school);
    }

    public function toggleActive(User $actor, User $target): bool
    {
        if (! $actor->is_active || $actor->is($target)) {
            return false;
        }

        if ($actor->isAdmin()) {
            return ! $target->isAdmin();
        }

        return $this->manage($actor, $target);
    }
}
