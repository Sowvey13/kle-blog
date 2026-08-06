<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        $title = $this->faker->words(3, true);

        return [
            'title' => ucfirst($title),
            'slug' => Str::slug($title),
            'content' => $this->faker->paragraphs(4, true),
            'is_active' => true,
        ];
    }
}
