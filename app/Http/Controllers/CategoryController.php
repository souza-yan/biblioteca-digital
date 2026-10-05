<?php

namespace App\Http\Controllers;

use App\Actions\Category\CreateCategory;
use App\Actions\Category\ToggleCategoryActive;
use App\Actions\Category\UpdateCategory;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Category::class);

        $categories = Category::query()
            ->when($request->user()->isTeacher(), fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->paginate(15);

        return response()->json($categories);
    }

    public function store(StoreCategoryRequest $request, CreateCategory $createCategory): JsonResponse
    {
        Gate::authorize('create', Category::class);

        $category = $createCategory->handle($request->user(), $request->validated());

        return response()->json($category, 201);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        UpdateCategory $updateCategory,
    ): JsonResponse {
        Gate::authorize('update', $category);

        $category = $updateCategory->handle($request->user(), $category, $request->validated());

        return response()->json($category);
    }

    public function toggleActive(
        Request $request,
        Category $category,
        ToggleCategoryActive $toggleCategoryActive,
    ): JsonResponse {
        Gate::authorize('toggleActive', $category);

        $category = $toggleCategoryActive->handle($request->user(), $category);

        return response()->json($category);
    }
}
