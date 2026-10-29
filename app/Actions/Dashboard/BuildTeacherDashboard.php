<?php

namespace App\Actions\Dashboard;

use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Models\ActivityLog;
use App\Models\Download;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class BuildTeacherDashboard
{
    /**
     * @return array<string, mixed>
     */
    public function handle(User $teacher): array
    {
        return [
            'isTeacher' => true,
            'publishedMaterials' => Material::query()
                ->with('category')
                ->where('status', MaterialStatus::PUBLISHED)
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->limit(3)
                ->get(),
            'favoriteMaterials' => $teacher->favorites()
                ->with('category')
                ->where('materials.status', MaterialStatus::PUBLISHED)
                ->orderBy('materials.title')
                ->limit(5)
                ->get(),
            'recentlyAccessedMaterials' => $this->recentlyAccessedMaterials($teacher),
        ];
    }

    /**
     * @return EloquentCollection<int, Material>
     */
    private function recentlyAccessedMaterials(User $teacher): EloquentCollection
    {
        $downloads = Download::query()
            ->select('material_id')
            ->selectRaw('downloaded_at as accessed_at')
            ->whereBelongsTo($teacher);
        $previews = ActivityLog::query()
            ->select('material_id')
            ->selectRaw('created_at as accessed_at')
            ->whereBelongsTo($teacher)
            ->where('action', ActivityAction::MATERIAL_PREVIEWED->value)
            ->whereNotNull('material_id');
        $latestAccesses = DB::query()
            ->fromSub($downloads->unionAll($previews), 'access_events')
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
            ->limit(3)
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
