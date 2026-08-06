<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_posts_are_listed(): void
    {
        Post::factory()->create(['is_approved' => true]);
        Post::factory()->create(['is_approved' => false]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_post_and_pending_approval(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/posts', [
            'title' => 'Test Başlık',
            'category_id' => $category->id,
            'content' => 'Test içerik açıklaması burada yer almaktadır.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Başlık',
            'is_approved' => false,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_only_delete_own_post(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $forbiddenResponse = $this->actingAs($otherUser, 'sanctum')->deleteJson('/api/posts/'.$post->id);
        $forbiddenResponse->assertStatus(403);

        $successResponse = $this->actingAs($user, 'sanctum')->deleteJson('/api/posts/'.$post->id);
        $successResponse->assertStatus(200);
    }
}
