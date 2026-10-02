<?php

namespace App\Actions\User;

use App\Models\User;

class ToggleUserActive
{
    public function handle(User $actor, User $user): User
    {
        abort_if($user->is($actor), 422, 'Você não pode desativar a si mesmo.');

        $user->update(['is_active' => ! $user->is_active]);

        return $user;
    }
}
