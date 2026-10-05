<?php

namespace App\Actions\Activity;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Material;
use App\Models\User;

class LogActivity
{
    public function handle(
        User $actor,
        ActivityAction $action,
        string $description,
        ?Material $material = null,
    ): ActivityLog {
        return ActivityLog::query()->create([
            'user_id' => $actor->getKey(),
            'material_id' => $material?->getKey(),
            'action' => $action,
            'description' => $description,
        ]);
    }
}
