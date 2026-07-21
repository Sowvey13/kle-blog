<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
      
        $defaultCategories = collect(['Teknoloji', 'Yazılım', 'Yaşam']);
        
        $defaultCategories->each(function ($name) {
            Category::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name),
                ]
            );
        });
    }
}