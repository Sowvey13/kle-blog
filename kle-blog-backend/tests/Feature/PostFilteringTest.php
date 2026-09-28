<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PostFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_and_category_filters_are_combined(): void
    {
        $laravel = Category::factory()->create();
        $other = Category::factory()->create();

        Post::factory()->for($laravel)->create(['title' => 'Livewire ile bileşenler']);
        Post::factory()->for($laravel)->create(['title' => 'Filament panel kurulumu']);
        Post::factory()->for($other)->create(['title' => 'Livewire başka kategoride']);

        $this->getJson('/api/posts?'.http_build_query(['search' => 'Livewire', 'category_id' => $laravel->id]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Livewire ile bileşenler');
    }

    public function test_user_and_date_filters_are_combined(): void
    {
        $author = User::factory()->create();

        $match = Post::factory()->for($author)->create(['title' => 'Eşleşen']);
        $match->forceFill(['created_at' => '2026-03-15 10:00:00'])->save();

        $sameAuthorOtherDay = Post::factory()->for($author)->create(['title' => 'Başka gün']);
        $sameAuthorOtherDay->forceFill(['created_at' => '2026-03-16 10:00:00'])->save();

        $otherAuthorSameDay = Post::factory()->create(['title' => 'Başka yazar']);
        $otherAuthorSameDay->forceFill(['created_at' => '2026-03-15 12:00:00'])->save();

        $this->getJson('/api/posts?'.http_build_query(['user_id' => $author->id, 'date' => '2026-03-15']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Eşleşen');
    }

    public function test_all_filters_together_exclude_unpublished_posts(): void
    {
        $author = User::factory()->create();
        $category = Category::factory()->create();

        $published = Post::factory()->for($author)->for($category)->create(['title' => 'Yayında Laravel']);
        $published->forceFill(['created_at' => '2026-04-01 09:00:00'])->save();

        $pending = Post::factory()->for($author)->for($category)->create([
            'title' => 'Onaysız Laravel',
            'is_approved' => false,
            'published_at' => null,
        ]);
        $pending->forceFill(['created_at' => '2026-04-01 09:30:00'])->save();

        $this->getJson('/api/posts?'.http_build_query([
            'search' => 'Laravel',
            'category_id' => $category->id,
            'user_id' => $author->id,
            'date' => '2026-04-01',
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Yayında Laravel')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_filters_without_matches_return_empty_page(): void
    {
        Post::factory()->create(['title' => 'Var olan yazı']);

        $this->getJson('/api/posts?search=olmayan-kelime')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_default_page_size_is_fifteen(): void
    {
        Post::factory()->count(16)->create();

        $this->getJson('/api/posts')
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_boundary_page_sizes_are_accepted(): void
    {
        Post::factory()->count(3)->create();

        $this->getJson('/api/posts?per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.last_page', 3);

        $this->getJson('/api/posts?per_page=50')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.per_page', 50);
    }

    public static function invalidPageSizes(): array
    {
        return [
            'zero' => ['0'],
            'negative' => ['-5'],
            'above maximum' => ['51'],
            'non numeric' => ['abc'],
        ];
    }

    #[DataProvider('invalidPageSizes')]
    public function test_out_of_range_page_sizes_are_rejected(string $perPage): void
    {
        $this->getJson('/api/posts?per_page='.$perPage)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_invalid_filter_values_are_rejected(): void
    {
        $this->getJson('/api/posts?'.http_build_query(['category_id' => 999, 'user_id' => 999, 'date' => 'dun']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id', 'user_id', 'date']);
    }

    public function test_page_beyond_last_returns_empty_data_with_meta(): void
    {
        Post::factory()->count(2)->create();

        $this->getJson('/api/posts?per_page=1&page=5')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.current_page', 5)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_category_detail_paginates_with_limits(): void
    {
        $category = Category::factory()->create(['name' => 'Sayfalı']);
        Post::factory()->count(17)->for($category)->create();

        $this->getJson('/api/categories/sayfali')
            ->assertOk()
            ->assertJsonPath('category.slug', 'sayfali')
            ->assertJsonCount(15, 'posts.data')
            ->assertJsonPath('posts.meta.per_page', 15)
            ->assertJsonPath('posts.meta.total', 17);

        $this->getJson('/api/categories/sayfali?per_page=5&page=4')
            ->assertOk()
            ->assertJsonCount(2, 'posts.data')
            ->assertJsonPath('posts.meta.current_page', 4)
            ->assertJsonPath('posts.meta.last_page', 4);

        $this->getJson('/api/categories/sayfali?per_page=51')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_my_posts_paginates_with_limits(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(11)->for($user)->create(['is_approved' => false, 'published_at' => null]);
        Post::factory()->count(2)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/my-posts')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 11)
            ->assertJsonPath('meta.per_page', 10);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/my-posts?per_page=50')
            ->assertOk()
            ->assertJsonCount(11, 'data');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/my-posts?per_page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }
}
