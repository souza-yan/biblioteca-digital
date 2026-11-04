<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialVersion;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

class MaterialThumbnailController extends Controller
{
    private const IMAGE_MIME_TYPES = [
        'image/png',
        'image/jpeg',
        'image/webp',
        'image/gif',
    ];

    public function show(Material $material): Response
    {
        Gate::authorize('download', $material);

        $version = $material->currentVersion;

        abort_unless($version instanceof MaterialVersion, 404);
        abort_unless(in_array($version->mime_type, self::IMAGE_MIME_TYPES, true), 404);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($version->file_path), 404);

        $response = response()->file($disk->path($version->file_path), [
            'Content-Type' => $version->mime_type,
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $version->original_name,
                Str::ascii($version->original_name) ?: 'image',
            ),
            'X-Content-Type-Options' => 'nosniff',
        ]);

        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');

        return $response;
    }
}
