<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    abstract protected function slugSourceColumn(): string;

    public static function bootHasSlug(): void
    {
        static::saving(function (self $model): void {
            if (! $model->shouldRefreshSlug()) {
                return;
            }

            $model->setAttribute('slug', static::uniqueSlugFor(
                $model->slugBase(),
                $model->exists ? $model->getKey() : null,
            ));
        });
    }

    public static function uniqueSlugFor(string $value, int|string|null $ignoreKey = null): string
    {
        $base = Str::slug($value, '-', 'tr');

        if ($base === '') {
            $base = Str::slug(class_basename(static::class));
        }

        $slug = $base;
        $suffix = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreKey !== null, fn ($query) => $query->whereKeyNot($ignoreKey))
            ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function shouldRefreshSlug(): bool
    {
        if (blank($this->getAttribute('slug')) || ! $this->exists) {
            return true;
        }

        return $this->isDirty('slug') || $this->isDirty($this->slugSourceColumn());
    }

    private function slugBase(): string
    {
        $slug = (string) $this->getAttribute('slug');

        if (filled($slug) && ($this->isDirty('slug') || ! $this->exists)) {
            return $slug;
        }

        return (string) $this->getAttribute($this->slugSourceColumn());
    }
}
