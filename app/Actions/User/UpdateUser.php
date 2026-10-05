<?php

namespace App\Actions\User;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUser
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, User $user, array $attributes): User
    {
        if (empty($attributes['password'])) {
            unset($attributes['password']);
        }

        return DB::transaction(function () use ($actor, $user, $attributes): User {
            $user->update($attributes);

            $this->logActivity->handle($actor, ActivityAction::USER_UPDATED, 'Usuário atualizado.');

            return $user;
        });
    }
}
