<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_categories(): void
    {
        $admin = User::factory()->admin()->create();

        $created = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/categories', ['name' => 'Yapay Zeka', 'is_active' => false])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'yapay-zeka')
            ->assertJsonPath('data.is_active', false);

        $categoryId = $created->json('data.id');

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/categories/'.$categoryId, ['is_active' => true])
            ->assertOk()
            ->assertJsonPath('data.name', 'Yapay Zeka')
            ->assertJsonPath('data.is_active', true);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/categories/'.$categoryId)
            ->assertOk();

        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
    }

    public function test_regular_user_cannot_create_categories(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/categories', ['name' => 'Yetkisiz'])
            ->assertForbidden();

        $this->assertDatabaseMissing('categories', ['name' => 'Yetkisiz']);
    }

    public function test_regular_user_cannot_update_categories_even_with_invalid_payload(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Korunan']);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/categories/'.$category->id, ['name' => 'Değişti'])
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/categories/'.$category->id, ['name' => ''])
            ->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Korunan']);
    }

    public function test_regular_user_cannot_delete_categories(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/categories/'.$category->id)
            ->assertForbidden();

        $this->assertModelExists($category);
    }

    public function test_guest_cannot_manage_categories(): void
    {
        $category = Category::factory()->create();

        $this->postJson('/api/categories', ['name' => 'Misafir'])->assertUnauthorized();
        $this->putJson('/api/categories/'.$category->id, ['name' => 'Misafir'])->assertUnauthorized();
        $this->deleteJson('/api/categories/'.$category->id)->assertUnauthorized();
    }

    public function test_admin_category_name_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Teknoloji']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/categories', ['name' => 'Teknoloji'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }
}
