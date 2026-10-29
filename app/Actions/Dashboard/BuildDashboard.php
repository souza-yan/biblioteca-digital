<?php

namespace App\Actions\Dashboard;

use App\Models\User;

class BuildDashboard
{
    public function __construct(
        private BuildTeacherDashboard $buildTeacherDashboard,
        private BuildManagementDashboard $buildManagementDashboard,
    ) {}

    /** @return array<string, mixed> */
    public function handle(User $actor, string $requestedDownloadPeriod = '30'): array
    {
        if ($actor->isTeacher()) {
            return $this->buildTeacherDashboard->handle($actor);
        }

        return $this->buildManagementDashboard->handle($actor, $requestedDownloadPeriod);
    }
}
