<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

class MaterialCoverController extends Controller
{
    public function show(Material $material): Response
    {
        Gate::authorize('download', $material);

        $version = $material->currentVersion;

        abort_unless($version instanceof MaterialVersion, 404);

        return $this->serveCover($material, $version);
    }

    public function version(Request $request, Material $material, MaterialVersion $version): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User && ($user->isAdmin() || $user->isStaff()), 403);
        abort_if((int) $version->material_id !== (int) $material->getKey(), 404);
        Gate::authorize('download', $material);

        return $this->serveCover($material, $version);
    }

    private function serveCover(Material $material, MaterialVersion $version): Response
    {
        abort_if((int) $material->current_version_id !== (int) $version->getKey() && request()->user()?->isTeacher(), 403);
        abort_unless(is_string($version->cover_path) && $version->cover_path !== '', 404);

        $disk = Storage::disk((string) config('materials.pdf_cover.disk'));

        abort_unless($disk->exists($version->cover_path), 404);

        $response = response()->file($disk->path($version->cover_path), [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                'capa-'.$version->getKey().'.jpg',
                Str::ascii('capa-'.$version->getKey().'.jpg'),
            ),
            'X-Content-Type-Options' => 'nosniff',
        ]);

        $response->setPrivate();
        $response->setMaxAge(3600);

        return $response;
    }
}
