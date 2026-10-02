<?php

namespace App\Actions\Material;

use App\Models\Material;

class UpdateMaterial
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Material $material, array $attributes): Material
    {
        $material->update($attributes);

        return $material;
    }
}
