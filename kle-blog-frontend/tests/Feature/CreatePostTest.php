<?php

namespace Tests\Feature;

use App\Livewire\CreatePost;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        session([
            'user_token' => 'test-token',
            'user' => ['id' => 1, 'name' => 'Yazar', 'role' => 'user'],
        ]);
    }

    public function test_successful_post_create_redirects_home(): void
    {
        Http::fake([
            '*/api/categories' => Http::response(['data' => [['id' => 1, 'name' => 'Yazılım']]], 200),
            '*/api/posts' => Http::response([
                'message' => 'Yazınız oluşturuldu ve onay için admin onayına gönderildi.',
                'data' => [
                    'id' => 44,
                    'title' => 'Yeni Yazı Başlığı',
                    'is_approved' => false,
                ],
            ], 201),
        ]);

        Livewire::test(CreatePost::class)
            ->set('title', 'Yeni Yazı Başlığı')
            ->set('category_id', '1')
            ->set('content', 'Bu yeterince uzun bir içeriktir.')
            ->call('savePost')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));
    }

    public function test_create_post_does_not_treat_error_message_as_success(): void
    {
        Http::fake([
            '*/api/categories' => Http::response(['data' => [['id' => 1, 'name' => 'Yazılım']]], 200),
            '*/api/posts' => Http::response([
                'message' => 'Yazınız oluşturuldu ve onay için admin onayına gönderildi.',
            ], 500),
        ]);

        Livewire::test(CreatePost::class)
            ->set('title', 'Yeni Yazı Başlığı')
            ->set('category_id', '1')
            ->set('content', 'Bu yeterince uzun bir içeriktir.')
            ->call('savePost')
            ->assertHasErrors(['api_error'])
            ->assertNoRedirect()
            ->assertSee('Yazı şu anda kaydedilemedi. Lütfen daha sonra tekrar deneyin.');
    }

    public function test_create_post_shows_error_on_unauthorized(): void
    {
        $this->assertFailureStatus(401, 'Oturumunuz sona ermiş olabilir. Lütfen tekrar giriş yapın.');
    }

    public function test_create_post_shows_error_on_forbidden(): void
    {
        $this->assertFailureStatus(403, 'Bu işlemi gerçekleştirme yetkiniz yok.');
    }

    public function test_create_post_shows_validation_error_on_unprocessable(): void
    {
        Http::fake([
            '*/api/categories' => Http::response(['data' => [['id' => 1, 'name' => 'Yazılım']]], 200),
            '*/api/posts' => Http::response([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'category_id' => ['Seçilen kategori aktif değil veya bulunamadı.'],
                ],
            ], 422),
        ]);

        Livewire::test(CreatePost::class)
            ->set('title', 'Yeni Yazı Başlığı')
            ->set('category_id', '1')
            ->set('content', 'Bu yeterince uzun bir içeriktir.')
            ->call('savePost')
            ->assertHasErrors(['api_error'])
            ->assertNoRedirect()
            ->assertSee('Seçilen kategori aktif değil veya bulunamadı.');
    }

    private function assertFailureStatus(int $status, string $expectedMessage): void
    {
        Http::fake([
            '*/api/categories' => Http::response(['data' => [['id' => 1, 'name' => 'Yazılım']]], 200),
            '*/api/posts' => Http::response(['message' => 'Hata'], $status),
        ]);

        Livewire::test(CreatePost::class)
            ->set('title', 'Yeni Yazı Başlığı')
            ->set('category_id', '1')
            ->set('content', 'Bu yeterince uzun bir içeriktir.')
            ->call('savePost')
            ->assertHasErrors(['api_error'])
            ->assertNoRedirect()
            ->assertSee($expectedMessage);
    }
}
