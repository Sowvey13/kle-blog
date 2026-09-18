<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Arr;

class UpdatePostAction
{
    public function execute(Post $post, array $data, User $user): Post
    {
        $payload = Arr::only($data, ['title', 'category_id', 'content']);

        if ($user->isAdmin()) {
            $payload = array_merge($payload, Arr::only($data, ['is_approved', 'published_at']));
        } else {
            $payload['is_approved'] = false;
            $payload['published_at'] = null;
        }

        $post->update($payload);

        return $post->refresh();
    }
}
