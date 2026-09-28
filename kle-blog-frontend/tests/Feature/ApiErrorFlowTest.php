<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard;
use App\Livewire\Home;
use App\Livewire\PostDetail;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ApiErrorFlowTest extends TestCase
{
    private const GENERIC_MESSAGE = 'İstek işlenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.';

    public function test_home_shows_safe_message_when_posts_api_fails(): void
    {
        Http::fake([
            '*/api/categories*' => Http::response(['data' => []], 200),
            '*/api/posts*' => Http::response(['message' => 'SQLSTATE[HY000] connection refused'], 500),
        ]);

        Livewire::test(Home::class)
            ->assertSet('posts', [])
            ->assertSee('Yazılar şu anda yüklenemedi. Lütfen daha sonra tekrar deneyin.')
            ->assertDontSee('SQLSTATE')
            ->assertDontSee('Henüz eklenmiş bir blog yazısı bulunamadı.');
    }

    public function test_home_shows_safe_message_on_network_timeout(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28: Connection timed out'));

        Livewire::test(Home::class)
            ->assertSet('posts', [])
            ->assertSet('categories', [])
            ->assertSee('Yazılar şu anda yüklenemedi. Lütfen daha sonra tekrar deneyin.')
            ->assertDontSee('cURL error');
    }

    public function test_home_reports_invalid_filters_on_validation_error(): void
    {
        Http::fake([
            '*/api/categories*' => Http::response(['data' => []], 200),
            '*/api/posts*' => Http::response([
                'message' => 'The per page field must not be greater than 50.',
                'errors' => ['per_page' => ['The per page field must not be greater than 50.']],
            ], 422),
        ]);

        Livewire::test(Home::class)
            ->assertSee('Arama kriterleri geçersiz. Lütfen filtreleri kontrol edin.');
    }

    public function test_home_clears_error_after_successful_retry(): void
    {
        $attempt = 0;

        Http::fake(function (Request $request) use (&$attempt) {
            if (str_contains($request->url(), '/api/categories')) {
                return Http::response(['data' => []], 200);
            }

            $attempt++;

            return $attempt === 1
                ? Http::response([], 503)
                : Http::response(['data' => [], 'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0]], 200);
        });

        Livewire::test(Home::class)
            ->assertSee('Yazılar şu anda yüklenemedi.')
            ->set('search', 'laravel')
            ->assertSet('errorMessage', '')
            ->assertSee('Henüz eklenmiş bir blog yazısı bulunamadı.');
    }

    public function test_login_shows_generic_message_when_backend_is_unreachable(): void
    {
        Http::fake(fn () => throw new ConnectionException('Could not resolve host: backend'));

        Livewire::test(Login::class)
            ->set('email', 'user@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertSet('errorMessage', self::GENERIC_MESSAGE)
            ->assertNoRedirect();

        $this->assertNull(session('user_token'));
    }

    public function test_login_shows_throttle_message_on_too_many_attempts(): void
    {
        Http::fake([
            '*/api/login' => Http::response(['message' => 'Too Many Attempts.'], 429),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'user@example.com')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertSet('errorMessage', 'Çok fazla hatalı giriş denemesi yaptınız. Lütfen bir süre bekleyip tekrar deneyin.');
    }

    public function test_register_surfaces_first_backend_validation_error(): void
    {
        Http::fake([
            '*/api/register' => Http::response([
                'message' => 'The email has already been taken.',
                'errors' => ['email' => ['Bu e-posta adresi zaten kullanımda.']],
            ], 422),
        ]);

        Livewire::test(Register::class)
            ->set('name', 'Yeni Kullanıcı')
            ->set('email', 'var@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertSet('errorMessage', 'Bu e-posta adresi zaten kullanımda.')
            ->assertNoRedirect();
    }

    public function test_post_detail_shows_safe_message_when_api_is_down(): void
    {
        Http::fake([
            '*/api/posts/*' => Http::response(['message' => 'Server Error'], 500),
        ]);

        Livewire::test(PostDetail::class, ['slug' => 'ornek'])
            ->assertSet('post', null)
            ->assertSee('Yazı şu anda yüklenemedi. Lütfen daha sonra tekrar deneyin.')
            ->assertDontSee('Server Error');
    }

    public function test_comment_rejected_by_backend_validation_shows_backend_reason(): void
    {
        session([
            'user_token' => 'test-token',
            'user' => ['id' => 5, 'name' => 'Okur', 'role' => 'user'],
        ]);

        Http::fake([
            '*/api/posts/*' => Http::response(['data' => $this->postPayload()], 200),
            '*/api/comments' => Http::response([
                'message' => 'Yalnızca yayınlanmış yazılara yorum yapılabilir.',
                'errors' => ['post_id' => ['Yalnızca yayınlanmış yazılara yorum yapılabilir.']],
            ], 422),
        ]);

        Livewire::test(PostDetail::class, ['slug' => 'ornek'])
            ->set('content', 'Harika bir yazı olmuş.')
            ->call('saveComment')
            ->assertHasErrors(['api_error'])
            ->assertSet('successMessage', '')
            ->assertSee('Yalnızca yayınlanmış yazılara yorum yapılabilir.');
    }

    public function test_comment_on_expired_session_shows_reauthentication_message(): void
    {
        session([
            'user_token' => 'expired-token',
            'user' => ['id' => 5, 'name' => 'Okur', 'role' => 'user'],
        ]);

        Http::fake([
            '*/api/posts/*' => Http::response(['data' => $this->postPayload()], 200),
            '*/api/comments' => Http::response(['message' => 'Unauthenticated.'], 401),
        ]);

        Livewire::test(PostDetail::class, ['slug' => 'ornek'])
            ->set('content', 'Harika bir yazı olmuş.')
            ->call('saveComment')
            ->assertSee('Oturumunuz sona ermiş olabilir. Lütfen tekrar giriş yapın.')
            ->assertDontSee('Unauthenticated.');
    }

    public function test_deleting_someone_elses_comment_shows_forbidden_message(): void
    {
        session([
            'user_token' => 'test-token',
            'user' => ['id' => 5, 'name' => 'Okur', 'role' => 'user'],
        ]);

        Http::fake([
            '*/api/posts/*' => Http::response(['data' => $this->postPayload()], 200),
            '*/api/comments/*' => Http::response(['message' => 'This action is unauthorized.'], 403),
        ]);

        Livewire::test(PostDetail::class, ['slug' => 'ornek'])
            ->call('deleteComment', 77)
            ->assertHasErrors(['api_error'])
            ->assertSee('Bu işlemi gerçekleştirme yetkiniz yok.');
    }

    public function test_dashboard_profile_update_failure_keeps_session_user(): void
    {
        session([
            'user_token' => 'test-token',
            'user' => ['id' => 1, 'name' => 'Yazar', 'email' => 'yazar@example.com'],
        ]);

        Http::fake([
            '*/api/me' => Http::response(['data' => ['id' => 1, 'name' => 'Yazar', 'email' => 'yazar@example.com']], 200),
            '*/api/my-posts*' => Http::response(['data' => [], 'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0]], 200),
            '*/api/profile' => Http::response([
                'message' => 'The email has already been taken.',
                'errors' => ['email' => ['Bu e-posta adresi zaten kullanımda.']],
            ], 422),
        ]);

        Livewire::test(Dashboard::class)
            ->set('email', 'baskasi@example.com')
            ->call('updateProfile')
            ->assertSet('errorMessage', 'Bu e-posta adresi zaten kullanımda.')
            ->assertSet('successMessage', '');

        $this->assertSame('yazar@example.com', session('user.email'));
    }

    public function test_dashboard_shows_message_when_my_posts_request_times_out(): void
    {
        session([
            'user_token' => 'test-token',
            'user' => ['id' => 1, 'name' => 'Yazar', 'email' => 'yazar@example.com'],
        ]);

        Http::fake(fn () => throw new ConnectionException('Operation timed out'));

        Livewire::test(Dashboard::class)
            ->assertSet('name', 'Yazar')
            ->assertSet('myPosts', [])
            ->assertSee('Yazılarınız şu anda yüklenemedi. Lütfen daha sonra tekrar deneyin.')
            ->assertDontSee('Operation timed out');
    }

    private function postPayload(): array
    {
        return [
            'id' => 12,
            'title' => 'Örnek Yazı',
            'slug' => 'ornek',
            'content' => 'İçerik',
            'created_at' => '2026-09-18 10:00:00',
            'user' => ['id' => 9, 'name' => 'Yazar'],
            'category' => ['name' => 'Genel'],
            'comments' => [],
        ];
    }
}
