<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexMyPostsRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProfileController extends Controller
{
    public function posts(IndexMyPostsRequest $request): AnonymousResourceCollection
    {
        $posts = Post::with(['category', 'user'])
            ->whereBelongsTo($request->user())
            ->latest()
            ->paginate($request->perPage(10));

        return PostResource::collection($posts);
    }
}
