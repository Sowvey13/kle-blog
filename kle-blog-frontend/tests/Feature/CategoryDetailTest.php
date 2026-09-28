<?php

namespace Tests\Feature;

use App\Livewire\CategoryDetail;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryDetailTest extends TestCase
{
    public function test_category_detail_reads_backend_category_and_posts_data_contract(): void
    {
        Http::fake([
            '*/api/categories/teknoloji*' => Http::response($this->categoryPayload(page: 1, lastPage: 1), 200),
        ]);

        Livewire::test(CategoryDetail::class, ['slug' => 'teknoloji'])
            ->assertSet('category.name', 'Teknoloji')
            ->assertSet('posts.0.title', 'Sayfa 1 Yazısı')
            ->assertSet('pagination.current_page', 1)
            ->assertSet('pagination.last_page', 1)
            ->assertSet('errorMessage', '')
            ->assertSee('Teknoloji')
            ->assertSee('Yazılım ve donanım yazıları')
            ->assertSee('Sayfa 1 Yazısı')
            ->assertDontSee('Sonraki');
    }

    public function test_category_detail_sends_page_and_per_page_and_navigates_between_pages(): void
    {
        Http::fake(fn (Request $request) => Http::response(
            $this->categoryPayload(page: (int) ($request['page'] ?? 1), lastPage: 3),
            200,
        ));

        Livewire::test(CategoryDetail::class, ['slug' => 'teknoloji'])
            ->assertSee('Sayfa 1 Yazısı')
            ->assertSee('Önceki')
            ->assertSee('Sonraki')
            ->assertDontSee('Previous')
            ->assertDontSee('Next')
            ->call('nextPage')
            ->assertSet('page', 2)
            ->assertSet('pagination.current_page', 2)
            ->assertSee('Sayfa 2 Yazısı')
            ->call('gotoPage', 3)
            ->assertSee('Sayfa 3 Yazısı')
            ->call('gotoPage', 99)
            ->assertSet('page', 3)
            ->call('previousPage')
            ->assertSet('page', 2);

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/api/categories/teknoloji')
            && (int) $request['page'] === 2
            && (int) $request['per_page'] === 9);
    }

    public function test_per_page_query_parameter_is_forwarded_and_clamped(): void
    {
        Http::fake([
            '*/api/categories/teknoloji*' => Http::response($this->categoryPayload(page: 1, lastPage: 1), 200),
        ]);

        Livewire::withQueryParams(['per_page' => 500])
            ->test(CategoryDetail::class, ['slug' => 'teknoloji']);

        Http::assertSent(fn (Request $request) => (int) $request['per_page'] === 50);
    }

    public function test_category_detail_renders_empty_state_without_pagination(): void
    {
        Http::fake([
            '*/api/categories/bos*' => Http::response([
                'category' => ['id' => 2, 'name' => 'Boş Kategori', 'slug' => 'bos'],
                'posts' => [
                    'data' => [],
                    'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0, 'links' => []],
                ],
            ], 200),
        ]);

        Livewire::test(CategoryDetail::class, ['slug' => 'bos'])
            ->assertSet('posts', [])
            ->assertSee('Boş Kategori')
            ->assertSee('Bu kategoride henüz yayınlanmış yazı bulunmuyor.')
            ->assertDontSee('Önceki');
    }

    public function test_category_detail_handles_missing_category(): void
    {
        Http::fake([
            '*/api/categories/pasif*' => Http::response(['message' => 'No query results for model'], 404),
        ]);

        Livewire::test(CategoryDetail::class, ['slug' => 'pasif'])
            ->assertSet('category', [])
            ->assertSet('posts', [])
            ->assertSee('Kategori bulunamadı veya şu anda görüntülenemiyor.');
    }

    public function test_category_detail_handles_server_error(): void
    {
        Http::fake([
            '*/api/categories/teknoloji*' => Http::response(['message' => 'Server Error'], 500),
        ]);

        Livewire::test(CategoryDetail::class, ['slug' => 'teknoloji'])
            ->assertSet('category', [])
            ->assertSee('Kategori şu anda yüklenemedi. Lütfen daha sonra tekrar deneyin.')
            ->assertDontSee('Server Error');
    }

    public function test_category_detail_handles_timeout_without_leaking_exception(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28: Operation timed out after 8001 milliseconds'));

        Livewire::test(CategoryDetail::class, ['slug' => 'teknoloji'])
            ->assertSet('category', [])
            ->assertSet('posts', [])
            ->assertSee('Kategori şu anda yüklenemedi. Lütfen daha sonra tekrar deneyin.')
            ->assertDontSee('cURL error 28');
    }

    private function categoryPayload(int $page, int $lastPage): array
    {
        $page = max(1, $page);

        return [
            'category' => [
                'id' => 1,
                'name' => 'Teknoloji',
                'slug' => 'teknoloji',
                'description' => 'Yazılım ve donanım yazıları',
            ],
            'posts' => [
                'data' => [[
                    'id' => $page,
                    'title' => 'Sayfa '.$page.' Yazısı',
                    'slug' => 'sayfa-'.$page,
                    'content' => 'İçerik özeti',
                    'created_at' => '2026-09-18 10:00:00',
                ]],
                'links' => [
                    'first' => 'http://backend:8000/api/categories/teknoloji?page=1',
                    'last' => 'http://backend:8000/api/categories/teknoloji?page='.$lastPage,
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'per_page' => 9,
                    'total' => $lastPage * 9,
                    'links' => array_merge(
                        [['url' => null, 'label' => '&laquo; Previous', 'page' => $page > 1 ? $page - 1 : null, 'active' => false]],
                        array_map(
                            fn (int $number) => ['url' => null, 'label' => (string) $number, 'page' => $number, 'active' => $number === $page],
                            range(1, $lastPage),
                        ),
                        [['url' => null, 'label' => 'Next &raquo;', 'page' => $page < $lastPage ? $page + 1 : null, 'active' => false]],
                    ),
                ],
            ],
        ];
    }
}
