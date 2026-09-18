<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\Url;

trait InteractsWithApiPagination
{
    #[Url]
    public int $page = 1;

    public array $pagination = [
        'current_page' => 1,
        'last_page' => 1,
        'total' => 0,
        'links' => [],
    ];

    public function gotoPage(int $page): void
    {
        $this->page = max(1, $page);
        $this->onApiPageChanged();
    }

    public function nextPage(): void
    {
        $this->gotoPage($this->page + 1);
    }

    public function previousPage(): void
    {
        $this->gotoPage($this->page - 1);
    }

    public function resetPage(): void
    {
        $this->page = 1;
    }

    abstract public function onApiPageChanged(): void;

    public function hydratePagination(array $response): void
    {
        $meta = is_array($response['meta'] ?? null) ? $response['meta'] : [];
        $links = $meta['links'] ?? [];

        if (! is_array($links) || ! array_is_list($links)) {
            $links = [];
        }

        $this->pagination = [
            'current_page' => (int) ($meta['current_page'] ?? $response['current_page'] ?? $this->page),
            'last_page' => (int) ($meta['last_page'] ?? $response['last_page'] ?? 1),
            'total' => (int) ($meta['total'] ?? $response['total'] ?? 0),
            'links' => $links,
        ];
    }
}
