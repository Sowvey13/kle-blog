<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\User;

class CreatePostAction
{
    public function execute(array $data, User $user): Post
    {
        return Post::create([
            'user_id' => $user->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'is_approved' => false,
        ]);
    }
}
