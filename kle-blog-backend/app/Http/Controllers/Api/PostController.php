<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreatePostAction;
use App\Actions\UpdatePostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexPostRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function index(IndexPostRequest $request): AnonymousResourceCollection
    {
        $query = Post::with(['user', 'category'])
            ->published();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $posts = $query->latest()->paginate($request->perPage());

        return PostResource::collection($posts);
    }

    public function show(string $slug): PostResource
    {
        $post = Post::with(['user', 'category', 'comments' => function ($query) {
            $query->where('is_approved', true)->with('user');
        }])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        return new PostResource($post);
    }

    public function store(StorePostRequest $request, CreatePostAction $action): JsonResponse
    {
        $this->authorize('create', Post::class);

        $post = $action->execute($request->validated(), $request->user());

        return response()->json([
            'message' => 'Yazınız oluşturuldu ve onay için admin onayına gönderildi.',
            'data' => new PostResource($post->load(['user', 'category'])),
        ], 201);
    }

    public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $action): JsonResponse
    {
        $this->authorize('update', $post);

        $post = $action->execute($post, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Yazı güncellendi.',
            'data' => new PostResource($post->load(['user', 'category'])),
        ]);
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json([
            'message' => 'Yazı silindi.',
        ]);
    }
}
