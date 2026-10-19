<?php

namespace App\Actions\User;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;

class SendUserPasswordResetLink
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $actor, User $target): string
    {
        $lockedActor = User::query()->findOrFail($actor->getKey());
        $lockedTarget = User::query()->findOrFail($target->getKey());

        Gate::forUser($lockedActor)->authorize('resetPassword', $lockedTarget);

        $status = Password::broker(config('fortify.passwords'))
            ->sendResetLink(['email' => $lockedTarget->email]);

        if ($status === Password::RESET_LINK_SENT) {
            try {
                $this->logActivity->handle(
                    $lockedActor,
                    ActivityAction::USER_PASSWORD_RESET_REQUESTED,
                    'Solicitado link de redefinição de senha para usuário #'.$lockedTarget->getKey().'.',
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $status;
    }
}
