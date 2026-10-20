<?php

namespace App\Actions\User;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetUserPasswordToDefault
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
            Gate::forUser($lockedActor)->authorize('resetPassword', $target);

            $target->forceFill([
                'password' => Hash::make('password'),
                'remember_token' => Str::random(60),
            ])->save();

            if (config('session.driver') === 'database') {
                DB::connection(config('session.connection'))
                    ->table((string) config('session.table', 'sessions'))
                    ->where('user_id', $target->getKey())
                    ->delete();
            }

            $this->logActivity->handle(
                $lockedActor,
                ActivityAction::USER_PASSWORD_RESET_BY_ADMIN,
                'A senha de um usuário foi redefinida para o padrão pelo administrador.',
            );

            return $target;
        });
    }
}
