<?php

namespace App\Actions\Material;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PublishMaterial
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $actor, Material $material): Material
    {
        return DB::transaction(function () use ($actor, $material): Material {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->getKey());
            $lockedMaterial = Material::query()->lockForUpdate()->findOrFail($material->getKey());
            Gate::forUser($lockedActor)->authorize('publish', $lockedMaterial);

            abort_unless(
                $lockedMaterial->currentVersion()->exists(),
                422,
                'O material precisa ter uma versão atual para ser publicado.',
            );

            $lockedMaterial->update([
                'status' => MaterialStatus::PUBLISHED,
                'published_at' => now(),
                'archived_at' => null,
            ]);

            $this->logActivity->handle($lockedActor, ActivityAction::MATERIAL_PUBLISHED, 'Material publicado.', $lockedMaterial);

            return $lockedMaterial;
        });
    }
}
