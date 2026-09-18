<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Livewire\Home;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    public function test_home_sends_page_query_and_renders_pagination(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/api/categories')) {
                return Http::response(['data' => []], 200);
            }

            $page = (int) $request['page'];

            return Http::response([
                'data' => [[
                    'id' => $page,
                    'title' => 'Sayfa '.$page.' Yazısı',
                    'slug' => 'sayfa-'.$page,
                    'content' => 'İçerik',
                    'created_at' => '2026-09-18 12:00:00',
                    'category' => ['name' => 'Genel'],
                    'user' => ['name' => 'Yazar'],
                ]],
                'meta' => [
                    'current_page' => $page ?: 1,
                    'last_page' => 3,
                    'total' => 20,
                    'links' => [
                        ['url' => null, 'label' => '1', 'active' => ($page ?: 1) === 1, 'page' => 1],
                        ['url' => null, 'label' => '2', 'active' => $page === 2, 'page' => 2],
                        ['url' => null, 'label' => '3', 'active' => $page === 3, 'page' => 3],
                    ],
                ],
            ], 200);
        });

        Livewire::test(Home::class)
            ->assertSee('Sayfa 1 Yazısı')
            ->assertSee('Önceki')
            ->assertSee('Sonraki')
            ->call('gotoPage', 2)
            ->assertSee('Sayfa 2 Yazısı');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/posts')
                && (int) $request['page'] === 2;
        });
    }

    public function test_dashboard_paginates_my_posts(): void
    {
        session([
            'user_token' => 'test-token',
            'user' => ['id' => 1, 'name' => 'Yazar', 'email' => 'yazar@example.com'],
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/api/my-posts')) {
                $page = (int) ($request['page'] ?? 1);

                return Http::response([
                    'data' => [[
                        'id' => $page,
                        'title' => 'Panel Yazısı '.$page,
                        'is_approved' => true,
                        'category' => ['name' => 'Genel'],
                    ]],
                    'meta' => [
                        'current_page' => $page,
                        'last_page' => 2,
                        'total' => 12,
                        'links' => [
                            ['label' => '1', 'active' => $page === 1, 'page' => 1],
                            ['label' => '2', 'active' => $page === 2, 'page' => 2],
                        ],
                    ],
                ], 200);
            }

            return Http::response([
                'data' => ['id' => 1, 'name' => 'Yazar', 'email' => 'yazar@example.com'],
            ], 200);
        });

        Livewire::test(Dashboard::class)
            ->assertSee('Panel Yazısı 1')
            ->call('gotoPage', 2)
            ->assertSee('Panel Yazısı 2');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/my-posts')
                && (int) $request['page'] === 2;
        });
    }
}
