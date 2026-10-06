<?php

namespace App\Actions\User;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateUser
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, User $user, array $attributes): User
    {
        $target = $user->fresh();
        $requestedRole = $attributes['role'] ?? null;
        $requestedRoleValue = $requestedRole instanceof Role
            ? $requestedRole->value
            : $requestedRole;

        if (
            $requestedRoleValue !== null
            && $requestedRoleValue !== $target->role->value
            && Gate::forUser($actor)->denies('changeRole', $target)
        ) {
            throw new AuthorizationException;
        }

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        }

        return DB::transaction(function () use ($actor, $target, $attributes): User {
            $target->update($attributes);

            $this->logActivity->handle($actor, ActivityAction::USER_UPDATED, 'Usuário atualizado.');

            return $target;
        });
    }
}
