<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_categories_are_listed(): void
    {
        Category::factory()->create(['name' => 'Aktif Kategori', 'is_active' => true]);
        Category::factory()->create(['name' => 'Pasif Kategori', 'is_active' => false]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);
    }

    public function test_category_detail_returns_related_posts(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Teknoloji',
            'slug' => 'teknoloji',
            'is_active' => true,
        ]);

        Post::create([
            'title' => 'Test Başlık',
            'slug' => 'test-baslik',
            'content' => 'Test İçerik',
            'category_id' => $category->id,
            'user_id' => $user->id,
            'is_approved' => true,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/categories/teknoloji');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'category' => ['id', 'name', 'slug'],
                'posts' => ['data'],
            ]);
    }
}
