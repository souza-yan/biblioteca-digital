<?php

namespace App\Actions\Dashboard;

use App\Enums\MaterialStatus;
use App\Enums\Role;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Download;
use App\Models\Material;
use App\Models\School;
use App\Models\User;

class BuildManagementDashboard
{
    public function __construct(private BuildMaterialDownloadRanking $buildMaterialDownloadRanking) {}

    /** @return array<string, mixed> */
    public function handle(User $actor, string $requestedDownloadPeriod): array
    {
        $school = $this->schoolFor($actor);
        $statusCounts = Material::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $categoryTotals = Category::query()
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when is_active = ? then 1 else 0 end) as active', [true])
            ->first();
        $downloadPeriod = $this->buildMaterialDownloadRanking->normalizePeriod($requestedDownloadPeriod);

        return [
            'isTeacher' => false,
            'activeUsersCount' => User::query()
                ->where('is_active', true)
                ->when($school !== null, fn ($query) => $query->where('role', Role::TEACHER))
                ->when($school !== null, fn ($query) => $query->whereHas(
                    'schools',
                    fn ($schools) => $schools
                        ->where('schools.id', $school->getKey())
                        ->where('school_user.is_active', true),
                ))
                ->count(),
            'statusCounts' => collect(MaterialStatus::cases())
                ->mapWithKeys(fn (MaterialStatus $status): array => [$status->label() => (int) $statusCounts->get($status->value, 0)]),
            'recentDownloadsCount' => Download::query()
                ->where('downloaded_at', '>=', now()->subDays(30))
                ->when($school !== null, fn ($query) => $query->whereHas(
                    'user.schools',
                    fn ($schools) => $schools
                        ->where('schools.id', $school->getKey())
                        ->where('school_user.is_active', true),
                ))
                ->count(),
            'recentActivities' => ActivityLog::query()
                ->with(['user', 'material'])
                ->when($school !== null, fn ($query) => $query->whereHas(
                    'user.schools',
                    fn ($schools) => $schools
                        ->where('schools.id', $school->getKey())
                        ->where('school_user.is_active', true),
                ))
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(3)
                ->get(),
            'categoryCounts' => [
                'total' => (int) $categoryTotals->total,
                'active' => (int) $categoryTotals->active,
                'inactive' => (int) $categoryTotals->total - (int) $categoryTotals->active,
            ],
            'topDownloadedMaterials' => $this->buildMaterialDownloadRanking->handle($downloadPeriod, $school)->limit(3)->get(),
            'downloadPeriod' => $downloadPeriod,
            'downloadPeriodOptions' => $this->buildMaterialDownloadRanking->periodOptions(),
        ];
    }

    private function schoolFor(User $actor): ?School
    {
        $school = $actor->isStaff() ? $actor->school() : null;
        abort_unless(! $actor->isStaff() || $school !== null, 403);

        return $school;
    }
}
