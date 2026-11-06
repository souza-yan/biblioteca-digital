<?php

namespace App\Livewire\Dashboard;

use App\Actions\Dashboard\BuildTeacherDashboard;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy(isolate: false)]
class TeacherDashboardContent extends Component
{
    use InteractsWithCurrentUser;

    public function placeholder(): View
    {
        return view('livewire.dashboard.teacher-dashboard-content-placeholder');
    }

    public function render(BuildTeacherDashboard $buildTeacherDashboard): View
    {
        $teacher = $this->currentUser();
        abort_unless($teacher->isTeacher(), 403);

        return view('livewire.dashboard.teacher-dashboard-content', $buildTeacherDashboard->handle($teacher));
    }
}
