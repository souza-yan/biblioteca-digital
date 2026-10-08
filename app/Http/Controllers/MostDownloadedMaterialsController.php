<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\BuildMaterialDownloadRanking;
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

        return view('dashboard.most-downloaded', [
            'topDownloadedMaterials' => $buildMaterialDownloadRanking
                ->handle($downloadPeriod)
                ->paginate(20)
                ->withQueryString(),
            'downloadPeriod' => $downloadPeriod,
            'downloadPeriodOptions' => $buildMaterialDownloadRanking->periodOptions(),
        ]);
    }
}
