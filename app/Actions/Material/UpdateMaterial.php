<?php

namespace App\Actions\Material;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateMaterial
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, Material $material, array $attributes): Material
    {
        return DB::transaction(function () use ($actor, $material, $attributes): Material {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->getKey());
            $lockedMaterial = Material::query()->lockForUpdate()->findOrFail($material->getKey());
            Gate::forUser($lockedActor)->authorize('update', $lockedMaterial);
            $lockedMaterial->update($attributes);

            $this->logActivity->handle($lockedActor, ActivityAction::MATERIAL_UPDATED, 'Material atualizado.', $lockedMaterial);

            return $lockedMaterial;
        });
    }
}
