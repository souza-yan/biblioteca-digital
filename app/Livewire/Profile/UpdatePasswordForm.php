<?php

namespace App\Livewire\Profile;

use Illuminate\View\View;
use Laravel\Jetstream\Http\Livewire\UpdatePasswordForm as JetstreamUpdatePasswordForm;

class UpdatePasswordForm extends JetstreamUpdatePasswordForm
{
    public function placeholder(): View
    {
        return view('profile.update-password-form-skeleton');
    }
}
