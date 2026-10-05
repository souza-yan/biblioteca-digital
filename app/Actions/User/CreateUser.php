<?php

namespace App\Actions\User;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateUser
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): User
    {
        return DB::transaction(function () use ($actor, $attributes): User {
            $user = User::create($attributes);

            $this->logActivity->handle($actor, ActivityAction::USER_CREATED, 'Usuário criado.');

            return $user;
        });
    }
}
