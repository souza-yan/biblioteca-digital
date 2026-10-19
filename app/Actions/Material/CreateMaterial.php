<?php

namespace App\Actions\Material;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateMaterial
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): Material
    {
        Gate::forUser($actor)->authorize('create', Material::class);

        return DB::transaction(function () use ($actor, $attributes): Material {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->getKey());
            Gate::forUser($lockedActor)->authorize('create', Material::class);

            $material = Material::create(array_merge(
                $attributes,
                ['created_by' => $lockedActor->getKey()],
            ));

            $this->logActivity->handle($lockedActor, ActivityAction::MATERIAL_CREATED, 'Material criado.', $material);

            return $material;
        });
    }
}
