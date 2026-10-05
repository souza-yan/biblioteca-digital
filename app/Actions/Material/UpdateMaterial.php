<?php

namespace App\Actions\Material;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateMaterial
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, Material $material, array $attributes): Material
    {
        return DB::transaction(function () use ($actor, $material, $attributes): Material {
            $material->update($attributes);

            $this->logActivity->handle($actor, ActivityAction::MATERIAL_UPDATED, 'Material atualizado.', $material);

            return $material;
        });
    }
}
