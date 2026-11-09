<?php

namespace App\Actions\User;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\User;
use App\Rules\Cpf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class UpdateUserCpf
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $actor, User $target, string $cpf): void
    {
        DB::transaction(function () use ($actor, $target, $cpf): void {
            $users = User::query()->whereKey([$actor->getKey(), $target->getKey()])
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $lockedActor = $users->get($actor->getKey());
            $lockedTarget = $users->get($target->getKey());
            abort_unless($lockedActor instanceof User && $lockedTarget instanceof User, 404);
            Gate::forUser($lockedActor)->authorize('updateCpf', $lockedTarget);

            $attributes = Validator::make(['cpf' => Cpf::normalize($cpf)], [
                'cpf' => Cpf::rules($lockedTarget->getKey()),
            ], Cpf::messages())->validate();
            $lockedTarget->update($attributes);
            $this->logActivity->handle($lockedActor, ActivityAction::USER_UPDATED, 'CPF do usuário atualizado.');
        });
    }
}
