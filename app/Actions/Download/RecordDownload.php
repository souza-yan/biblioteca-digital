<?php

namespace App\Actions\Download;

use App\Models\Download;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;

class RecordDownload
{
    public function handle(User $user, Material $material, MaterialVersion $version): Download
    {
        return $material->downloads()->create([
            'user_id' => $user->getKey(),
            'material_version_id' => $version->getKey(),
            'downloaded_at' => now(),
        ]);
    }
}
