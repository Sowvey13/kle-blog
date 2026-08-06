<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'post_id' => ['required', 'exists:posts,id'],
            'content' => ['required', 'string'],
        ]);

        $comment = $request->user()->comments()->create([
            'post_id' => $validated['post_id'],
            'content' => $validated['content'],
            'is_approved' => false,
        ]);

        return response()->json([
            'message' => 'Yorumunuz alındı, admin onayından sonra yayınlanacaktır.',
            'data' => new CommentResource($comment->load('user')),
        ], 201);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Yorum silindi.',
        ]);
    }
}