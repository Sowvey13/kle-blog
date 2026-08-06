<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {

        $defaultCategories = ['Yazılım', 'Teknoloji', 'Yaşam'];

        foreach ($defaultCategories as $name) {

            Category::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name),
                    'is_active' => true,
                ]
            );
        }
    }
}
