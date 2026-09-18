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
        Post::factory()->create(['is_approved' => true, 'published_at' => now()]);
        Post::factory()->create(['is_approved' => false]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_posts_in_inactive_categories_are_hidden_from_public_list_and_search(): void
    {
        $active = Category::factory()->create(['is_active' => true]);
        $inactive = Category::factory()->create(['is_active' => false]);

        Post::factory()->create([
            'category_id' => $active->id,
            'title' => 'Görünür Yazı',
            'is_approved' => true,
            'published_at' => now(),
        ]);

        Post::factory()->create([
            'category_id' => $inactive->id,
            'title' => 'Gizli Benzersiz Başlık',
            'is_approved' => true,
            'published_at' => now(),
        ]);

        $this->getJson('/api/posts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Görünür Yazı');

        $this->getJson('/api/posts?search='.urlencode('Gizli Benzersiz Başlık'))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_per_page_is_capped_at_fifty(): void
    {
        $this->getJson('/api/posts?per_page=51')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_user_can_create_post_and_pending_approval(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['is_active' => true]);

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

    public function test_post_cannot_be_created_in_inactive_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['is_active' => false]);

        $this->actingAs($user, 'sanctum')->postJson('/api/posts', [
            'title' => 'Pasif Kategori Yazısı',
            'category_id' => $category->id,
            'content' => 'Test içerik açıklaması burada yer almaktadır.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_owner_update_resets_editorial_approval(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'is_approved' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')->putJson('/api/posts/'.$post->id, [
            'title' => 'Güncellenmiş Başlık',
            'content' => 'Güncellenmiş içerik metni burada yer alır.',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Güncellenmiş Başlık')
            ->assertJsonPath('data.is_approved', false);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Güncellenmiş Başlık',
            'is_approved' => false,
            'published_at' => null,
        ]);
    }

    public function test_non_owner_cannot_update_post(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other, 'sanctum')->patchJson('/api/posts/'.$post->id, [
            'title' => 'Yetkisiz Güncelleme',
        ])->assertForbidden();
    }

    public function test_admin_can_update_post_and_keep_approval_state(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create([
            'is_approved' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($admin, 'sanctum')->putJson('/api/posts/'.$post->id, [
            'title' => 'Admin Düzenlemesi',
        ])->assertOk();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Admin Düzenlemesi',
            'is_approved' => true,
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
