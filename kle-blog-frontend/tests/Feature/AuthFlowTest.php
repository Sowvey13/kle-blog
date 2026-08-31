<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    public function test_login_successful_redirects_and_sets_session(): void
    {
        Http::fake([
            '*/api/login' => Http::response([
                'token' => 'fake-sanctum-token',
                'user' => ['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com'],
            ], 200),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('home'));

        $this->assertEquals('fake-sanctum-token', session('user_token'));
    }

    public function test_login_failed_shows_error(): void
    {
        Http::fake([
            '*/api/login' => Http::response([
                'message' => 'Geçersiz e-posta veya şifre.',
            ], 401),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'wrong@example.com')
            ->set('password', 'wrongpass')
            ->call('login')
            ->assertSet('errorMessage', 'Girdiğiniz e-posta adresi veya şifre hatalı.');
    }
}
