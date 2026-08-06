<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }

    public function posts(Request $request)
    {
        $posts = Post::with(['category', 'user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return PostResource::collection($posts);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil bilgileriniz başarıyla güncellendi.',
            'data' => $user,
        ]);
    }
}