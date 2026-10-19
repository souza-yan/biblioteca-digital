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

class CreateUser
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): User
    {
        $schoolId = $attributes['school_id'] ?? null;
        $attributes = array_intersect_key($attributes, array_flip(['name', 'email', 'password', 'role']));

        return DB::transaction(function () use ($actor, $attributes, $schoolId): User {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->getKey());
            Gate::forUser($lockedActor)->authorize('create', User::class);

            $role = $attributes['role'] ?? null;
            $role = $role instanceof Role ? $role : (is_string($role) ? Role::tryFrom($role) : null);

            if ($role === null) {
                throw ValidationException::withMessages([
                    'role' => 'Selecione um cargo válido.',
                ]);
            }

            if ($lockedActor->isStaff()) {
                if ($role !== Role::TEACHER || $schoolId !== null) {
                    throw new AuthorizationException;
                }

                $school = $lockedActor->school();

                abort_unless($school !== null, 403);
                $school = School::query()
                    ->whereKey($school->getKey())
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();
            } elseif ($role === Role::STAFF) {
                if ($schoolId === null) {
                    throw ValidationException::withMessages([
                        'school_id' => 'Selecione uma escola ativa para a equipe de gestão.',
                    ]);
                }

                $school = School::query()
                    ->whereKey($schoolId)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();
            } elseif ($schoolId !== null) {
                $school = School::query()
                    ->whereKey($schoolId)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();
            } else {
                $school = null;
            }

            $user = User::create($attributes);

            if ($school !== null && ! $user->isAdmin()) {
                $user->schools()->attach($school->getKey(), ['is_active' => true]);
            }

            $this->logActivity->handle($lockedActor, ActivityAction::USER_CREATED, 'Usuário criado.');

            return $user;
        });
    }
}
