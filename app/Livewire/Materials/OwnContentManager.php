<?php

namespace App\Livewire\Materials;

use App\Livewire\Concerns\InteractsWithCurrentUser;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class OwnContentManager extends Component
{
    use InteractsWithCurrentUser;

    #[Layout('layouts.app')]
    public function render(): View
    {
        $actor = $this->currentUser();
        abort_unless($actor->is_active && $actor->isStaff() && $actor->hasExactlyOneActiveSchool(), 403);

        return view('livewire.materials.own-content-manager');
    }
}
