<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contract;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SlugGenerationTest extends TestCase
{
    use RefreshDatabase;

    public static function sluggableModels(): array
    {
        return [
            'post' => [Post::class, 'title'],
            'category' => [Category::class, 'name'],
            'contract' => [Contract::class, 'title'],
        ];
    }

    #[DataProvider('sluggableModels')]
    public function test_slug_is_generated_from_source_with_turkish_transliteration(string $model, string $source): void
    {
        $record = $this->makeRecord($model, [$source => 'Çok Güzel Şeyler İçin Ağaç']);

        $this->assertSame('cok-guzel-seyler-icin-agac', $record->slug);
    }

    #[DataProvider('sluggableModels')]
    public function test_duplicate_sources_receive_incrementing_suffixes(string $model, string $source): void
    {
        $slugs = collect(range(1, 3))
            ->map(fn () => $this->makeRecord($model, [$source => 'Aynı Başlık'])->slug)
            ->all();

        $this->assertSame(['ayni-baslik', 'ayni-baslik-1', 'ayni-baslik-2'], $slugs);
    }

    #[DataProvider('sluggableModels')]
    public function test_updating_source_regenerates_a_unique_slug(string $model, string $source): void
    {
        $this->makeRecord($model, [$source => 'Hedef Başlık']);
        $record = $this->makeRecord($model, [$source => 'Eski Başlık']);

        $record->update([$source => 'Hedef Başlık']);

        $this->assertSame('hedef-baslik-1', $record->refresh()->slug);
    }

    #[DataProvider('sluggableModels')]
    public function test_resaving_same_source_keeps_existing_slug(string $model, string $source): void
    {
        $record = $this->makeRecord($model, [$source => 'Sabit Başlık']);

        $record->update([$source => 'Sabit Başlık']);
        $record->update([$source => 'SABİT başlık']);

        $this->assertSame('sabit-baslik', $record->refresh()->slug);
    }

    #[DataProvider('sluggableModels')]
    public function test_updating_other_attributes_does_not_touch_slug(string $model, string $source): void
    {
        $record = $this->makeRecord($model, [$source => 'Değişmeyen']);
        $record->forceFill(['slug' => 'ozel-link'])->save();

        $record->touch();

        $this->assertSame('ozel-link', $record->refresh()->slug);
    }

    #[DataProvider('sluggableModels')]
    public function test_explicit_slug_is_normalized_and_deduplicated(string $model, string $source): void
    {
        $this->makeRecord($model, [$source => 'Birinci', 'slug' => 'ozel-link']);
        $record = $this->makeRecord($model, [$source => 'İkinci', 'slug' => 'Özel Link']);

        $this->assertSame('ozel-link-1', $record->slug);
    }

    #[DataProvider('sluggableModels')]
    public function test_blank_slug_on_update_is_regenerated_from_source(string $model, string $source): void
    {
        $record = $this->makeRecord($model, [$source => 'Yeniden Üret', 'slug' => 'manuel']);

        $record->update(['slug' => '']);

        $this->assertSame('yeniden-uret', $record->refresh()->slug);
    }

    public function test_api_created_posts_with_same_title_get_unique_slugs(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $payload = [
            'title' => 'API Başlığı',
            'category_id' => $category->id,
            'content' => 'Yeterince uzun bir içerik metni.',
        ];

        $first = $this->actingAs($user, 'sanctum')->postJson('/api/posts', $payload)->assertCreated();
        $second = $this->actingAs($user, 'sanctum')->postJson('/api/posts', $payload)->assertCreated();

        $this->assertSame('api-basligi', $first->json('data.slug'));
        $this->assertSame('api-basligi-1', $second->json('data.slug'));
    }

    public function test_api_post_title_update_regenerates_slug(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['title' => 'İlk Başlık']);
        Post::factory()->create(['title' => 'Yeni Başlık']);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/posts/'.$post->id, ['title' => 'Yeni Başlık'])
            ->assertOk()
            ->assertJsonPath('data.slug', 'yeni-baslik-1');
    }

    public function test_api_category_rename_regenerates_slug(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Eski Ad']);

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/categories/'.$category->id, ['name' => 'Yeni Ad'])
            ->assertOk()
            ->assertJsonPath('data.slug', 'yeni-ad');
    }

    private function makeRecord(string $model, array $attributes): Model
    {
        return $model::factory()->create($attributes);
    }
}
