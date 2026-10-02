<?php

namespace App\Http\Controllers;

use App\Actions\MaterialVersion\CreateMaterialVersion;
use App\Http\Requests\StoreMaterialVersionRequest;
use App\Models\Material;
use App\Models\MaterialVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class MaterialVersionController extends Controller
{
    public function index(Material $material): JsonResponse
    {
        Gate::authorize('viewAny', [MaterialVersion::class, $material]);

        $versions = $material->versions()
            ->orderByDesc('version_number')
            ->paginate(15);

        return response()->json($versions);
    }

    public function store(
        StoreMaterialVersionRequest $request,
        Material $material,
        CreateMaterialVersion $createMaterialVersion,
    ): JsonResponse {
        Gate::authorize('create', [MaterialVersion::class, $material]);

        $uploadedFile = $request->file('file');

        if (! $uploadedFile instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'file' => 'O arquivo da versão é obrigatório.',
            ]);
        }

        $version = $createMaterialVersion->handle(
            $material,
            $request->user(),
            $uploadedFile,
            $request->validated('change_note'),
        );

        return response()->json($version, 201);
    }
}
