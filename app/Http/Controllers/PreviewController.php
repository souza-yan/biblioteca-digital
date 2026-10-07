<?php

namespace App\Http\Controllers;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

class PreviewController extends Controller
{
    private const SUPPORTED_MIME_TYPES = [
        'application/pdf',
        'image/png',
        'image/jpeg',
        'image/webp',
        'image/gif',
        'text/plain',
        'video/mp4',
        'video/webm',
        'audio/mpeg',
    ];

    public function show(Material $material, LogActivity $logActivity): Response
    {
        Gate::authorize('download', $material);

        $version = $material->currentVersion;

        abort_unless($version instanceof MaterialVersion, 404);

        return $this->servePreview($material, $version, $logActivity);
    }

    public function version(
        Request $request,
        Material $material,
        MaterialVersion $version,
        LogActivity $logActivity,
    ): Response {
        Gate::authorize('download', $material);

        $user = $request->user();

        abort_unless($user instanceof User && ($user->isAdmin() || $user->isStaff()), 403);
        abort_if((int) $version->material_id !== (int) $material->getKey(), 404);

        return $this->servePreview($material, $version, $logActivity);
    }

    private function servePreview(
        Material $material,
        MaterialVersion $version,
        LogActivity $logActivity,
    ): Response {
        abort_unless(in_array($version->mime_type, self::SUPPORTED_MIME_TYPES, true), 404);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($version->file_path), 404);

        $user = request()->user();

        abort_unless($user instanceof User, 403);

        $headers = [
            'Content-Type' => $version->mime_type === 'text/plain'
                ? 'text/html; charset=UTF-8'
                : $version->mime_type,
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $version->original_name,
                Str::ascii($version->original_name) ?: 'file',
            ),
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; media-src 'self'; style-src 'unsafe-inline'; sandbox",
            'X-Frame-Options' => 'SAMEORIGIN',
        ];

        if ($version->mime_type === 'text/plain') {
            $response = response()->view('previews.text', [
                'content' => $disk->get($version->file_path),
            ], 200, $headers);
        } else {
            $response = response()->file($disk->path($version->file_path), $headers);
        }

        $logActivity->handle(
            $user,
            ActivityAction::MATERIAL_PREVIEWED,
            'Prévia do material consultada.',
            $material,
        );

        return $response;
    }
}
