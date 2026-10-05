<?php

namespace App\Actions\Material;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ArchiveMaterial
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $actor, Material $material): Material
    {
        return DB::transaction(function () use ($actor, $material): Material {
            $material->update([
                'status' => MaterialStatus::ARCHIVED,
                'archived_at' => now(),
            ]);

            $this->logActivity->handle($actor, ActivityAction::MATERIAL_ARCHIVED, 'Material arquivado.', $material);

            return $material;
        });
    }
}
