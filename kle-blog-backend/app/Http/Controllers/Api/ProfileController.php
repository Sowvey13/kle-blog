<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function posts(Request $request)
    {
        $posts = Post::with(['category', 'user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return PostResource::collection($posts);
    }
}
