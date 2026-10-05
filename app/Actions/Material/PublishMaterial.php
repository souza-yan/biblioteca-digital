<?php

namespace App\Actions\Material;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PublishMaterial
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $actor, Material $material): Material
    {
        return DB::transaction(function () use ($actor, $material): Material {
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

            $this->logActivity->handle($actor, ActivityAction::MATERIAL_PUBLISHED, 'Material publicado.', $material);

            return $material;
        });
    }
}
