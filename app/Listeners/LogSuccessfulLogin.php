<?php

namespace App\Listeners;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\User;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->logActivity->handle(
            $event->user,
            ActivityAction::AUTH_LOGIN,
            'Login realizado.',
        );
    }
}
