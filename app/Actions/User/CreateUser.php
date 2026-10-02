<?php

namespace App\Actions\User;

use App\Models\User;

class CreateUser
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes): User
    {
        return User::create($attributes);
    }
}
