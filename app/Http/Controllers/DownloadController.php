<?php

namespace App\Http\Controllers;

use App\Actions\Download\RecordDownload;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function show(
        Request $request,
        Material $material,
        RecordDownload $recordDownload,
    ): StreamedResponse {
        Gate::authorize('download', $material);

        $version = $material->currentVersion;

        if (! $version instanceof MaterialVersion) {
            abort(404, 'Arquivo não encontrado.');
        }

        return $this->downloadVersion($request, $material, $version, $recordDownload);
    }

    public function version(
        Request $request,
        Material $material,
        MaterialVersion $version,
        RecordDownload $recordDownload,
    ): StreamedResponse {
        Gate::authorize('download', $material);

        $user = $request->user();

        abort_unless($user instanceof User && ($user->isAdmin() || $user->isStaff()), 403);
        abort_if((int) $version->material_id !== (int) $material->getKey(), 404);

        return $this->downloadVersion($request, $material, $version, $recordDownload);
    }

    private function downloadVersion(
        Request $request,
        Material $material,
        MaterialVersion $version,
        RecordDownload $recordDownload,
    ): StreamedResponse {
        $disk = Storage::disk('local');

        if (! $disk->exists($version->file_path)) {
            abort(404, 'Arquivo não encontrado.');
        }

        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $recordDownload->handle($user, $material, $version);

        return $disk->download($version->file_path, $version->original_name);
    }
}
