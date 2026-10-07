<?php

namespace App\Listeners;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\User;
use Illuminate\Auth\Events\Logout;

class LogLogout
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(Logout $event): void
    {
        if (! $event->user instanceof User || ! User::query()->whereKey($event->user->getKey())->exists()) {
            return;
        }

        $this->logActivity->handle(
            $event->user,
            ActivityAction::AUTH_LOGOUT,
            'Logout realizado.',
        );
    }
}
