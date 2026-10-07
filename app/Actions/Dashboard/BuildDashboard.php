<?php

namespace App\Actions\Dashboard;

use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Download;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BuildDashboard
{
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
                    ->limit(5)
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

        $downloadPeriodOptions = [
            '30' => 'Últimos 30 dias',
            '90' => 'Últimos 90 dias',
            'total' => 'Todo o período',
        ];
        $downloadPeriod = array_key_exists($requestedDownloadPeriod, $downloadPeriodOptions)
            ? $requestedDownloadPeriod
            : '30';

        $statusCounts = Material::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $categoryTotals = Category::query()
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when is_active = ? then 1 else 0 end) as active', [true])
            ->first();

        $downloadCutoff = $downloadPeriod === 'total'
            ? null
            : now()->subDays((int) $downloadPeriod);

        return [
            'isTeacher' => false,
            'activeUsersCount' => User::query()->where('is_active', true)->count(),
            'statusCounts' => collect(MaterialStatus::cases())
                ->mapWithKeys(fn (MaterialStatus $status): array => [
                    $status->label() => (int) $statusCounts->get($status->value, 0),
                ]),
            'recentDownloadsCount' => Download::query()
                ->where('downloaded_at', '>=', now()->subDays(30))
                ->count(),
            'recentActivities' => ActivityLog::query()
                ->with(['user', 'material'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(10)
                ->get(),
            'categoryCounts' => [
                'total' => (int) $categoryTotals->total,
                'active' => (int) $categoryTotals->active,
                'inactive' => (int) $categoryTotals->total - (int) $categoryTotals->active,
            ],
            'topDownloadedMaterials' => Material::query()
                ->with('category')
                ->withCount([
                    'downloads as downloads_count' => fn (Builder $query): Builder => $downloadCutoff === null
                        ? $query
                        : $query->where('downloaded_at', '>=', $downloadCutoff),
                ])
                ->having('downloads_count', '>', 0)
                ->orderByDesc('downloads_count')
                ->orderBy('title')
                ->limit(5)
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
