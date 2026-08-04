<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $posts = Post::with(['category', 'user'])
            ->where('is_approved', true)
            ->latest()
            ->paginate(10);

        return PostResource::collection($posts);
    }

    public function show(string $slug): PostResource
    {
        $post = Post::with(['category', 'user', 'comments.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('view', $post);

        return new PostResource($post);
    }

    public function myPosts(Request $request): AnonymousResourceCollection
    {
        $posts = Post::with(['category'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return PostResource::collection($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $post = $request->user()->posts()->create([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_approved' => false,
        ]);

        return response()->json([
            'message' => 'Yazı oluşturuldu ve onay için gönderildi.',
            'data' => new PostResource($post->load(['category', 'user'])),
        ], 201);
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json([
            'message' => 'Yazı başarıyla silindi.',
        ]);
    }
}