<?php

namespace App\Actions\Dashboard;

use App\Enums\MaterialStatus;
use App\Models\ActivityLog;
use App\Models\Download;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class BuildDashboard
{
    /**
     * @return array{
     *     isTeacher: bool,
     *     publishedMaterials?: EloquentCollection<int, Material>,
     *     favoriteMaterials?: EloquentCollection<int, Material>,
     *     activeUsersCount?: int,
     *     statusCounts?: Collection<string, int>,
     *     recentDownloadsCount?: int,
     *     recentActivities?: EloquentCollection<int, ActivityLog>
     * }
     */
    public function handle(User $actor): array
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
            ];
        }

        $statusCounts = Material::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

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
        ];
    }
}
