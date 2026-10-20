<?php

namespace App\Actions\User;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class ResetOwnPasswordToDefault
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $actor): User
    {
        return DB::transaction(function () use ($actor): User {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->getKey());

            Gate::forUser($lockedActor)->authorize('resetOwnPasswordToDefault', $lockedActor);

            $lockedActor->forceFill([
                'password' => Hash::make('password'),
            ])->save();

            $this->logActivity->handle(
                $lockedActor,
                ActivityAction::USER_PASSWORD_RESET_TO_DEFAULT,
                'O usuário redefiniu a própria senha para o padrão.',
            );

            return $lockedActor;
        });
    }
}
