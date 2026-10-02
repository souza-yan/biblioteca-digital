<?php

namespace App\Actions\Material;

use App\Models\Material;
use App\Models\User;

class CreateMaterial
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): Material
    {
        return Material::create(array_merge(
            $attributes,
            ['created_by' => $actor->getKey()],
        ));
    }
}
