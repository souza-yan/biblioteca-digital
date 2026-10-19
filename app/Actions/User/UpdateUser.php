<?php

namespace App\Actions\User;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Enums\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UpdateUser
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, User $user, array $attributes): User
    {
        $hasGlobalAccountAttributes = array_intersect(
            array_keys($attributes),
            ['name', 'email', 'password', 'password_confirmation'],
        ) !== [];
        $requestedRole = $attributes['role'] ?? null;
        $requestedRoleValue = $requestedRole instanceof Role
            ? $requestedRole->value
            : $requestedRole;

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        }

        $schoolId = $attributes['school_id'] ?? null;
        $attributes = array_intersect_key($attributes, array_flip(['name', 'email', 'password', 'role']));

        return DB::transaction(function () use ($actor, $user, $attributes, $schoolId, $requestedRoleValue, $hasGlobalAccountAttributes): User {
            $lockedUsers = User::query()
                ->whereKey(collect([$actor->getKey(), $user->getKey()])->unique()->sort()->values())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $lockedActor = $lockedUsers->get($actor->getKey());
            $target = $lockedUsers->get($user->getKey());

            abort_unless($lockedActor instanceof User && $target instanceof User, 404);
            Gate::forUser($lockedActor)->authorize('manage', $target);

            if (! $lockedActor->isAdmin() && ($schoolId !== null || $hasGlobalAccountAttributes)) {
                throw new AuthorizationException;
            }

            if ($requestedRoleValue !== null && ! is_string($requestedRoleValue)) {
                throw ValidationException::withMessages(['role' => 'Selecione um cargo válido.']);
            }

            $finalRole = $requestedRoleValue === null
                ? $target->role
                : Role::tryFrom($requestedRoleValue);

            if (! $finalRole instanceof Role) {
                throw ValidationException::withMessages(['role' => 'Selecione um cargo válido.']);
            }

            if ($finalRole !== $target->role) {
                Gate::forUser($lockedActor)->authorize('changeRole', $target);
            }

            $school = null;

            if ($lockedActor->isAdmin() && $finalRole === Role::STAFF) {
                if ($schoolId !== null) {
                    $school = School::query()
                        ->whereKey($schoolId)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->firstOrFail();
                } else {
                    $school = $target->school();

                    if ($school === null) {
                        throw ValidationException::withMessages([
                            'school_id' => 'Selecione uma escola ativa para a equipe de gestão.',
                        ]);
                    }
                }
            } elseif ($lockedActor->isAdmin() && $schoolId !== null && $finalRole !== Role::ADMIN) {
                $school = School::query()
                    ->whereKey($schoolId)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            if ($school !== null && $lockedActor->isAdmin() && $finalRole === Role::STAFF) {
                $target->schools()->newPivotStatement()
                    ->where('user_id', $target->getKey())
                    ->update(['is_active' => false]);

                $target->schools()->syncWithoutDetaching([
                    $school->getKey() => ['is_active' => true],
                ]);
            } elseif ($school !== null && $lockedActor->isAdmin() && $finalRole !== Role::ADMIN) {
                $target->schools()->syncWithoutDetaching([
                    $school->getKey() => ['is_active' => true],
                ]);
            } elseif (
                $lockedActor->isAdmin()
                && $target->role !== Role::ADMIN
                && $finalRole === Role::ADMIN
            ) {
                $target->schools()->newPivotStatement()
                    ->where('user_id', $target->getKey())
                    ->update(['is_active' => false]);
            }

            $target->update($attributes);

            $this->logActivity->handle($lockedActor, ActivityAction::USER_UPDATED, 'Usuário atualizado.');

            return $target;
        });
    }
}
