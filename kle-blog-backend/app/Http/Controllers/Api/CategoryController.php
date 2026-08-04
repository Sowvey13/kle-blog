<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::where('is_active', true)->get();

        return CategoryResource::collection($categories);
    }

    public function show(string $slug): CategoryResource
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return new CategoryResource($category);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'is_active' => ['boolean'],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Kategori oluşturuldu.',
            'data' => new CategoryResource($category),
        ], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.$category->id],
            'is_active' => ['boolean'],
        ]);

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