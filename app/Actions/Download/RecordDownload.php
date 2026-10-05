<?php

namespace App\Actions\Download;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Download;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecordDownload
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $user, Material $material, MaterialVersion $version): Download
    {
        return DB::transaction(function () use ($user, $material, $version): Download {
            $download = $material->downloads()->create([
                'user_id' => $user->getKey(),
                'material_version_id' => $version->getKey(),
                'downloaded_at' => now(),
            ]);

            $this->logActivity->handle(
                $user,
                ActivityAction::MATERIAL_DOWNLOADED,
                'Material baixado.',
                $material,
            );

            return $download;
        });
    }
}
