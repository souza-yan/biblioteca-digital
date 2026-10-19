<?php

namespace App\Actions\Material;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ArchiveMaterial
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $actor, Material $material): Material
    {
        return DB::transaction(function () use ($actor, $material): Material {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->getKey());
            $lockedMaterial = Material::query()->lockForUpdate()->findOrFail($material->getKey());
            Gate::forUser($lockedActor)->authorize('archive', $lockedMaterial);
            $lockedMaterial->update([
                'status' => MaterialStatus::ARCHIVED,
                'archived_at' => now(),
            ]);

            $this->logActivity->handle($lockedActor, ActivityAction::MATERIAL_ARCHIVED, 'Material arquivado.', $lockedMaterial);

            return $lockedMaterial;
        });
    }
}
