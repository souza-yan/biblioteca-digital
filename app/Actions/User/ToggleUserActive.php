<?php

namespace App\Actions\User;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ToggleUserActive
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $actor, User $user): User
    {
        abort_if($user->is($actor), 422, 'Você não pode desativar a si mesmo.');

        return DB::transaction(function () use ($actor, $user): User {
            $user->update(['is_active' => ! $user->is_active]);

            $this->logActivity->handle($actor, ActivityAction::USER_TOGGLED, 'Status do usuário alterado.');

            return $user;
        });
    }
}
