<?php

namespace App\Actions\Dashboard;

use App\Models\Material;
use App\Models\School;
use Illuminate\Database\Eloquent\Builder;

class BuildMaterialDownloadRanking
{
    /**
     * @var array<string, string>
     */
    private const DOWNLOAD_PERIOD_OPTIONS = [
        '30' => 'Últimos 30 dias',
        '90' => 'Últimos 90 dias',
        'total' => 'Todo o período',
    ];

    /**
     * @return array<string, string>
     */
    public function periodOptions(): array
    {
        return self::DOWNLOAD_PERIOD_OPTIONS;
    }

    public function normalizePeriod(string $requestedPeriod): string
    {
        return array_key_exists($requestedPeriod, self::DOWNLOAD_PERIOD_OPTIONS)
            ? $requestedPeriod
            : '30';
    }

    /**
     * @return Builder<Material>
     */
    public function handle(string $requestedPeriod = '30', ?School $school = null): Builder
    {
        $period = $this->normalizePeriod($requestedPeriod);
        $downloadCutoff = $period === 'total'
            ? null
            : now()->subDays((int) $period);

        return Material::query()
            ->with('category')
            ->withCount([
                'downloads as downloads_count' => function (Builder $query) use ($downloadCutoff, $school): Builder {
                    if ($downloadCutoff !== null) {
                        $query->where('downloaded_at', '>=', $downloadCutoff);
                    }

                    if ($school !== null) {
                        $query->whereHas('user.schools', fn (Builder $schools): Builder => $schools
                            ->where('schools.id', $school->getKey())
                            ->where('schools.is_active', true)
                            ->where('school_user.is_active', true));
                    }

                    return $query;
                },
            ])
            ->having('downloads_count', '>', 0)
            ->orderByDesc('downloads_count')
            ->orderBy('title')
            ->orderBy('id');
    }
}
