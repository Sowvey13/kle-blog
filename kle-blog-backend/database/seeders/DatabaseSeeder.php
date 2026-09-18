<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@kleblog.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'role' => UserRole::ADMIN,
            ]
        );

        $this->call(CategorySeeder::class);
    }
}
