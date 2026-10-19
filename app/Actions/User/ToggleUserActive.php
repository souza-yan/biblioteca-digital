<?php

namespace App\Actions\User;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ToggleUserActive
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $actor, User $user): User
    {
        return DB::transaction(function () use ($actor, $user): User {
            $lockedUsers = User::query()
                ->whereKey(collect([$actor->getKey(), $user->getKey()])->unique()->sort()->values())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $lockedActor = $lockedUsers->get($actor->getKey());
            $target = $lockedUsers->get($user->getKey());

            abort_unless($lockedActor instanceof User && $target instanceof User, 404);
            Gate::forUser($lockedActor)->authorize('toggleActive', $target);

            if ($lockedActor->isStaff()) {
                $school = $lockedActor->school();

                if ($school === null || ! $target->hasSchoolMembership($school)) {
                    throw new AuthorizationException;
                }

                $pivot = DB::table('school_user')
                    ->where('school_id', $school->getKey())
                    ->where('user_id', $target->getKey())
                    ->lockForUpdate()
                    ->first();

                if ($pivot === null) {
                    throw new AuthorizationException;
                }

                DB::table('school_user')
                    ->where('school_id', $school->getKey())
                    ->where('user_id', $target->getKey())
                    ->update([
                        'is_active' => ! (bool) $pivot->is_active,
                        'updated_at' => now(),
                    ]);
            } else {
                $target->update(['is_active' => ! $target->is_active]);
            }

            $this->logActivity->handle($lockedActor, ActivityAction::USER_TOGGLED, 'Status do usuário alterado.');

            return $target->fresh();
        });
    }
}
