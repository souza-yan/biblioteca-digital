<?php

namespace App\Livewire\Profile;

use App\Actions\User\ResetOwnPasswordToDefault;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ResetDefaultPassword extends Component
{
    public bool $showConfirmation = false;

    public function openConfirmation(): void
    {
        $actor = auth()->user();
        abort_unless($actor instanceof User, 403);
        Gate::authorize('resetOwnPasswordToDefault', $actor);

        $this->showConfirmation = true;
    }

    public function closeConfirmation(): void
    {
        $this->showConfirmation = false;
    }

    public function resetPassword(ResetOwnPasswordToDefault $resetOwnPasswordToDefault): void
    {
        $actor = auth()->user();
        abort_unless($actor instanceof User, 403);

        Gate::authorize('resetOwnPasswordToDefault', $actor);
        $updatedActor = $resetOwnPasswordToDefault->handle($actor);
        $this->showConfirmation = false;

        Auth::guard('web')->setUser($updatedActor);
        Auth::guard('web')->logoutOtherDevices('password');
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirectRoute('login');
    }

    public function render(): View
    {
        return view('livewire.profile.reset-default-password');
    }
}
