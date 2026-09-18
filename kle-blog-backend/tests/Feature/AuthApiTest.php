<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'user', 'token']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['token']);

        $token = $loginResponse->json('token');

        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200);
    }

    public function test_authenticated_user_can_view_profile_via_me_and_profile_routes(): void
    {
        $user = User::factory()->create([
            'name' => 'Profil Kullanıcı',
            'email' => 'profil@example.com',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'profil@example.com')
            ->assertJsonPath('data.name', 'Profil Kullanıcı')
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role']]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Eski Ad',
            'email' => 'eski@example.com',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', [
                'name' => 'Yeni Ad',
                'email' => 'yeni@example.com',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Yeni Ad')
            ->assertJsonPath('data.email', 'yeni@example.com')
            ->assertJsonPath('message', 'Profil bilgileriniz başarıyla güncellendi.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Yeni Ad',
            'email' => 'yeni@example.com',
        ]);
    }
}
