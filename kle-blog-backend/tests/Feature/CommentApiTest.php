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
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'is_approved' => true,
            'published_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/comments', [
            'post_id' => $post->id,
            'content' => 'Bu bir test yorumudur.',
        ]);

        $response->assertStatus(201);
    }

    public function test_comment_is_rejected_for_unapproved_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'is_approved' => false,
            'published_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/comments', [
            'post_id' => $post->id,
            'content' => 'Onaysız yazıya yorum.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['post_id']);
    }

    public function test_comment_is_rejected_for_scheduled_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'is_approved' => true,
            'published_at' => now()->addDay(),
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/comments', [
            'post_id' => $post->id,
            'content' => 'İleri tarihli yazıya yorum.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['post_id']);
    }
}
