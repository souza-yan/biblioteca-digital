<?php

namespace App\Actions\School;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateSchool
{
    public function __construct(private LogActivity $logActivity) {}

    /** @param array<string, mixed> $attributes */
    public function handle(User $actor, School $school, array $attributes): School
    {
        return DB::transaction(function () use ($actor, $school, $attributes): School {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->getKey());
            $lockedSchool = School::query()->lockForUpdate()->findOrFail($school->getKey());
            Gate::forUser($lockedActor)->authorize('update', $lockedSchool);

            $lockedSchool->update($attributes);
            $this->logActivity->handle($lockedActor, ActivityAction::SCHOOL_UPDATED, 'Escola atualizada.');

            return $lockedSchool;
        });
    }
}
