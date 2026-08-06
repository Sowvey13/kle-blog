<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;

class CreatePostAction
{
    public function execute(array $data, User $user): Post
    {
        $slug = Str::slug($data['title']) . '-' . Str::random(5);

        return Post::create([
            'user_id' => $user->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'],
            'is_approved' => false,
        ]);
    }
}