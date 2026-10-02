<?php

namespace App\Actions\Material;

use App\Enums\MaterialStatus;
use App\Models\Material;

class PublishMaterial
{
    public function handle(Material $material): Material
    {
        abort_unless(
            $material->currentVersion()->exists(),
            422,
            'O material precisa ter uma versão atual para ser publicado.',
        );

        $material->update([
            'status' => MaterialStatus::PUBLISHED,
            'published_at' => now(),
            'archived_at' => null,
        ]);

        return $material;
    }
}
