<?php

namespace App\Http\Controllers;

use App\Actions\Material\ArchiveMaterial;
use App\Actions\Material\CreateMaterial;
use App\Actions\Material\PublishMaterial;
use App\Actions\Material\UpdateMaterial;
use App\Enums\MaterialStatus;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Material;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MaterialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Material::class);

        $materials = Material::query()
            ->when(
                $request->user()->isTeacher(),
                fn (Builder $query): Builder => $query->where('status', MaterialStatus::PUBLISHED->value),
            )
            ->orderBy('title')
            ->paginate(15);

        return response()->json($materials);
    }

    public function show(Material $material): JsonResponse
    {
        Gate::authorize('view', $material);

        return response()->json($material);
    }

    public function store(StoreMaterialRequest $request, CreateMaterial $createMaterial): JsonResponse
    {
        Gate::authorize('create', Material::class);

        $material = $createMaterial->handle($request->user(), $request->validated());

        return response()->json($material, 201);
    }

    public function update(
        UpdateMaterialRequest $request,
        Material $material,
        UpdateMaterial $updateMaterial,
    ): JsonResponse {
        Gate::authorize('update', $material);

        $material = $updateMaterial->handle($material, $request->validated());

        return response()->json($material);
    }

    public function publish(Material $material, PublishMaterial $publishMaterial): JsonResponse
    {
        Gate::authorize('publish', $material);

        $material = $publishMaterial->handle($material);

        return response()->json($material);
    }

    public function archive(Material $material, ArchiveMaterial $archiveMaterial): JsonResponse
    {
        Gate::authorize('archive', $material);

        $material = $archiveMaterial->handle($material);

        return response()->json($material);
    }
}
