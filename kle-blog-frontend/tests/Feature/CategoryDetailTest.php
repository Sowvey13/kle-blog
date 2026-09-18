<?php

namespace Tests\Feature;

use App\Livewire\CategoryDetail;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryDetailTest extends TestCase
{
    public function test_category_detail_reads_backend_category_and_posts_data_contract(): void
    {
        Http::fake([
            '*/api/categories/teknoloji' => Http::response([
                'category' => [
                    'id' => 1,
                    'name' => 'Teknoloji',
                    'slug' => 'teknoloji',
                    'description' => 'Yazılım ve donanım yazıları',
                ],
                'posts' => [
                    'data' => [
                        [
                            'id' => 10,
                            'title' => 'Laravel 13 Notları',
                            'slug' => 'laravel-13-notlari',
                            'content' => 'İçerik özeti',
                            'created_at' => '2026-09-18 10:00:00',
                        ],
                    ],
                    'meta' => [
                        'current_page' => 1,
                        'last_page' => 1,
                    ],
                ],
            ], 200),
        ]);

        Livewire::test(CategoryDetail::class, ['slug' => 'teknoloji'])
            ->assertSet('category.name', 'Teknoloji')
            ->assertSet('posts.0.title', 'Laravel 13 Notları')
            ->assertSet('errorMessage', '')
            ->assertSee('Teknoloji')
            ->assertSee('Yazılım ve donanım yazıları')
            ->assertSee('Laravel 13 Notları');
    }

    public function test_category_detail_handles_missing_category(): void
    {
        Http::fake([
            '*/api/categories/pasif' => Http::response([
                'message' => 'No query results for model',
            ], 404),
        ]);

        Livewire::test(CategoryDetail::class, ['slug' => 'pasif'])
            ->assertSet('category', [])
            ->assertSet('posts', [])
            ->assertSee('Kategori bulunamadı veya şu anda görüntülenemiyor.');
    }
}
