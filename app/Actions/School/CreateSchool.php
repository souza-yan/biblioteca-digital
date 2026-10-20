<?php

namespace App\Actions\School;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateSchool
{
    public function __construct(private LogActivity $logActivity) {}

    /** @param array<string, mixed> $attributes */
    public function handle(User $actor, array $attributes): School
    {
        return DB::transaction(function () use ($actor, $attributes): School {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->getKey());
            Gate::forUser($lockedActor)->authorize('create', School::class);

            $school = School::query()->create($attributes);

            $this->logActivity->handle($lockedActor, ActivityAction::SCHOOL_CREATED, 'Escola criada.');

            return $school;
        });
    }
}
