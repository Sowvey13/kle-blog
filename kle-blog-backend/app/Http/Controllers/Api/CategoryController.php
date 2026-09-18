<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexCategoryRequest;
use App\Http\Requests\ShowCategoryRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PostResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(IndexCategoryRequest $request): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->active()
            ->latest()
            ->paginate($request->perPage(10));

        return CategoryResource::collection($categories);
    }

    public function show(ShowCategoryRequest $request, string $slug): JsonResponse
    {
        $category = Category::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $posts = $category->posts()
            ->published()
            ->with(['user', 'category'])
            ->latest()
            ->paginate($request->perPage(15));

        return response()->json([
            'category' => new CategoryResource($category),
            'posts' => PostResource::collection($posts)->response()->getData(true),
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $validated = $request->validated();

        $category = Category::create([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Kategori oluşturuldu.',
            'data' => new CategoryResource($category),
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        $validated = $request->validated();

        $category->update($validated);

        return response()->json([
            'message' => 'Kategori güncellendi.',
            'data' => new CategoryResource($category),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return response()->json([
            'message' => 'Kategori silindi.',
        ]);
    }
}
