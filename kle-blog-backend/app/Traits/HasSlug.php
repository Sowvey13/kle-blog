<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            $source = filled($model->slug)
                ? (string) $model->slug
                : (string) ($model->title ?? $model->name ?? '');

            $model->slug = static::generateUniqueSlug($source, $model->getKey());
        });
    }

    protected static function generateUniqueSlug(string $value, int|string|null $ignoreId = null): string
    {
        $slug = Str::slug($value, '-', 'tr');
        $originalSlug = $slug;
        $count = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
