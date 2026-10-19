<?php

namespace App\Actions\Download;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Download;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RecordDownload
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $user, Material $material, MaterialVersion $version): Download
    {
        return DB::transaction(function () use ($user, $material, $version): Download {
            $currentUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $currentMaterial = Material::query()->lockForUpdate()->findOrFail($material->getKey());
            $currentVersion = MaterialVersion::query()->findOrFail($version->getKey());
            Gate::forUser($currentUser)->authorize('download', $currentMaterial);
            abort_if((int) $currentVersion->material_id !== (int) $currentMaterial->getKey(), 404);

            $download = $currentMaterial->downloads()->create([
                'user_id' => $currentUser->getKey(),
                'material_version_id' => $currentVersion->getKey(),
                'downloaded_at' => now(),
            ]);

            $this->logActivity->handle(
                $currentUser,
                ActivityAction::MATERIAL_DOWNLOADED,
                'Material baixado.',
                $currentMaterial,
            );

            return $download;
        });
    }
}
