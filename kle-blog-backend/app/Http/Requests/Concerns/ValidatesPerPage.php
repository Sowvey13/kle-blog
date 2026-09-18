<?php

namespace App\Http\Requests\Concerns;

trait ValidatesPerPage
{
    protected function perPageRules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function perPage(int $default = 15): int
    {
        return (int) ($this->validated('per_page') ?? $default);
    }
}
