<?php

namespace App\Actions\User;

use App\Models\User;

class UpdateUser
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $user, array $attributes): User
    {
        if (empty($attributes['password'])) {
            unset($attributes['password']);
        }

        $user->update($attributes);

        return $user;
    }
}
