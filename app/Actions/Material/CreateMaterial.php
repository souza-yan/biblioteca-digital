<?php

namespace App\Actions\Material;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateMaterial
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): Material
    {
        return DB::transaction(function () use ($actor, $attributes): Material {
            $material = Material::create(array_merge(
                $attributes,
                ['created_by' => $actor->getKey()],
            ));

            $this->logActivity->handle($actor, ActivityAction::MATERIAL_CREATED, 'Material criado.', $material);

            return $material;
        });
    }
}
