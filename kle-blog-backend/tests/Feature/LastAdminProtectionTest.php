<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LastAdminProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_admin_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->expectException(ValidationException::class);

        $admin->delete();
    }

    public function test_last_admin_cannot_be_demoted(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->expectException(ValidationException::class);

        $admin->update(['role' => UserRole::USER]);
    }

    public function test_admin_can_be_removed_when_another_admin_exists(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        User::factory()->create(['role' => UserRole::ADMIN]);

        $admin->update(['role' => UserRole::USER]);
        $admin->delete();

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }
}
