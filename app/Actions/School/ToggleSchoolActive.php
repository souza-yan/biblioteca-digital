<?php

namespace App\Actions\School;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ToggleSchoolActive
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $actor, School $school): School
    {
        return DB::transaction(function () use ($actor, $school): School {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->getKey());
            $lockedSchool = School::query()->lockForUpdate()->findOrFail($school->getKey());
            Gate::forUser($lockedActor)->authorize('update', $lockedSchool);

            $lockedSchool->update(['is_active' => ! $lockedSchool->is_active]);
            $this->logActivity->handle($lockedActor, ActivityAction::SCHOOL_TOGGLED, 'Status da escola alterado.');

            return $lockedSchool;
        });
    }
}
