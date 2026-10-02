<?php

namespace App\Actions\Material;

use App\Enums\MaterialStatus;
use App\Models\Material;

class ArchiveMaterial
{
    public function handle(Material $material): Material
    {
        $material->update([
            'status' => MaterialStatus::ARCHIVED,
            'archived_at' => now(),
        ]);

        return $material;
    }
}
