<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\BuildMaterialDownloadRanking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MostDownloadedMaterialsController extends Controller
{
    public function __invoke(Request $request, BuildMaterialDownloadRanking $buildMaterialDownloadRanking): View
    {
        $requestedPeriod = $request->query('downloadPeriod', '30');
        $downloadPeriod = $buildMaterialDownloadRanking->normalizePeriod(
            is_string($requestedPeriod) ? $requestedPeriod : '',
        );
        $actor = $request->user();
        $school = $actor instanceof User && $actor->isStaff() ? $actor->school() : null;

        abort_unless(! ($actor instanceof User) || ! $actor->isStaff() || $school !== null, 403);

        return view('dashboard.most-downloaded', [
            'topDownloadedMaterials' => $buildMaterialDownloadRanking
                ->handle($downloadPeriod, $school)
                ->paginate(20)
                ->withQueryString(),
            'downloadPeriod' => $downloadPeriod,
            'downloadPeriodOptions' => $buildMaterialDownloadRanking->periodOptions(),
        ]);
    }
}
