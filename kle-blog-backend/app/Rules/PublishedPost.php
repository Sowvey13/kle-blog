<?php

namespace App\Rules;

use App\Models\Post;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PublishedPost implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isPublished = Post::query()->published()->whereKey($value)->exists();

        if (! $isPublished) {
            $fail('Yalnızca yayınlanmış yazılara yorum yapılabilir.');
        }
    }
}
