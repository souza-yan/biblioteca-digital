<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm as JetstreamUpdateProfileInformationForm;

class UpdateProfileInformationForm extends JetstreamUpdateProfileInformationForm
{
    public function placeholder(): View
    {
        return view('profile.update-profile-information-form-skeleton');
    }

    public function updateProfileInformation(UpdatesUserProfileInformation $updater): void
    {
        $this->resetErrorBag();

        $updater->update(
            Auth::user(),
            $this->photo
                ? array_merge($this->state, ['photo' => $this->photo])
                : $this->state
        );

        $this->photo = null;

        $this->dispatch('saved');
        $this->dispatch('refresh-navigation-menu');
    }
}
