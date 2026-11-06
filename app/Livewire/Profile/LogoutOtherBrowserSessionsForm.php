<?php

namespace App\Livewire\Profile;

use Illuminate\View\View;
use Laravel\Jetstream\Http\Livewire\LogoutOtherBrowserSessionsForm as JetstreamLogoutOtherBrowserSessionsForm;

class LogoutOtherBrowserSessionsForm extends JetstreamLogoutOtherBrowserSessionsForm
{
    public function placeholder(): View
    {
        return view('profile.logout-other-browser-sessions-form-skeleton');
    }
}
