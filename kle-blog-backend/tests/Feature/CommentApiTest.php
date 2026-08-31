<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_comment_and_it_is_pending_approval(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['is_active' => true]);
        $post = Post::factory()->create(['user_id' => $user->id, 'category_id' => $category->id, 'is_approved' => true]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/comments', [
            'post_id' => $post->id,
            'content' => 'Bu bir test yorumudur.',
        ]);

        $response->assertStatus(201);
    }
}
