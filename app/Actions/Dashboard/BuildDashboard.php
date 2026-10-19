<?php

namespace App\Actions\Dashboard;

use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Download;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BuildDashboard
{
    public function __construct(private BuildMaterialDownloadRanking $buildMaterialDownloadRanking) {}

    /**
     * @return array{
     *     isTeacher: bool,
     *     publishedMaterials?: EloquentCollection<int, Material>,
     *     favoriteMaterials?: EloquentCollection<int, Material>,
     *     recentlyAccessedMaterials?: EloquentCollection<int, Material>,
     *     activeUsersCount?: int,
     *     statusCounts?: Collection<string, int>,
     *     recentDownloadsCount?: int,
     *     recentActivities?: EloquentCollection<int, ActivityLog>,
     *     categoryCounts?: array{total: int, active: int, inactive: int},
     *     topDownloadedMaterials?: EloquentCollection<int, Material>,
     *     downloadPeriod?: string,
     *     downloadPeriodOptions?: array<string, string>
     * }
     */
    public function handle(User $actor, string $requestedDownloadPeriod = '30'): array
    {
        if ($actor->isTeacher()) {
            return [
                'isTeacher' => true,
                'publishedMaterials' => Material::query()
                    ->with('category')
                    ->where('status', MaterialStatus::PUBLISHED)
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->limit(3)
                    ->get(),
                'favoriteMaterials' => $actor->favorites()
                    ->with('category')
                    ->where('materials.status', MaterialStatus::PUBLISHED)
                    ->orderBy('materials.title')
                    ->limit(5)
                    ->get(),
                'recentlyAccessedMaterials' => $this->recentlyAccessedMaterials($actor),
            ];
        }

        $school = $actor->isStaff() ? $actor->school() : null;
        abort_unless(! $actor->isStaff() || $school !== null, 403);

        $downloadPeriodOptions = $this->buildMaterialDownloadRanking->periodOptions();
        $downloadPeriod = $this->buildMaterialDownloadRanking->normalizePeriod($requestedDownloadPeriod);

        $statusCounts = Material::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $categoryTotals = Category::query()
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when is_active = ? then 1 else 0 end) as active', [true])
            ->first();

        return [
            'isTeacher' => false,
            'activeUsersCount' => User::query()
                ->where('is_active', true)
                ->when($school !== null, fn ($query) => $query->whereHas(
                    'schools',
                    fn ($schools) => $schools
                        ->where('schools.id', $school->getKey())
                        ->where('schools.is_active', true)
                        ->where('school_user.is_active', true),
                ))
                ->count(),
            'statusCounts' => collect(MaterialStatus::cases())
                ->mapWithKeys(fn (MaterialStatus $status): array => [
                    $status->label() => (int) $statusCounts->get($status->value, 0),
                ]),
            'recentDownloadsCount' => Download::query()
                ->where('downloaded_at', '>=', now()->subDays(30))
                ->when($school !== null, fn ($query) => $query->whereHas(
                    'user.schools',
                    fn ($schools) => $schools
                        ->where('schools.id', $school->getKey())
                        ->where('schools.is_active', true)
                        ->where('school_user.is_active', true),
                ))
                ->count(),
            'recentActivities' => ActivityLog::query()
                ->with(['user', 'material'])
                ->when($school !== null, fn ($query) => $query->whereHas(
                    'user.schools',
                    fn ($schools) => $schools
                        ->where('schools.id', $school->getKey())
                        ->where('schools.is_active', true)
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
            'topDownloadedMaterials' => $this->buildMaterialDownloadRanking
                ->handle($downloadPeriod, $school)
                ->limit(3)
                ->get(),
            'downloadPeriod' => $downloadPeriod,
            'downloadPeriodOptions' => $downloadPeriodOptions,
        ];
    }

    /**
     * @return EloquentCollection<int, Material>
     */
    private function recentlyAccessedMaterials(User $actor): EloquentCollection
    {
        $userId = $actor->getKey();
        $downloads = Download::query()
            ->select('material_id')
            ->selectRaw('downloaded_at as accessed_at')
            ->where('user_id', $userId);
        $previews = ActivityLog::query()
            ->select('material_id')
            ->selectRaw('created_at as accessed_at')
            ->where('user_id', $userId)
            ->where('action', ActivityAction::MATERIAL_PREVIEWED->value)
            ->whereNotNull('material_id');

        $accessEvents = $downloads->unionAll($previews);
        $latestAccesses = DB::query()
            ->fromSub($accessEvents, 'access_events')
            ->select('material_id')
            ->selectRaw('max(accessed_at) as last_accessed_at')
            ->groupBy('material_id');

        $materialIds = Material::query()
            ->joinSub($latestAccesses, 'latest_accesses', function (JoinClause $join): void {
                $join->on('materials.id', '=', 'latest_accesses.material_id');
            })
            ->where('materials.status', MaterialStatus::PUBLISHED->value)
            ->select('materials.id')
            ->orderByDesc('latest_accesses.last_accessed_at')
            ->orderByDesc('materials.id')
            ->limit(5)
            ->pluck('materials.id')
            ->all();

        if ($materialIds === []) {
            return new EloquentCollection;
        }

        $materials = Material::query()
            ->with('category')
            ->whereKey($materialIds)
            ->get()
            ->keyBy('id');

        return new EloquentCollection(
            collect($materialIds)
                ->map(fn (int $materialId): ?Material => $materials->get($materialId))
                ->filter()
                ->values()
                ->all(),
        );
    }
}
