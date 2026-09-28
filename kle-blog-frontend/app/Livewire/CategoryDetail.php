<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithApiPagination;
use App\Services\ApiService;
use Livewire\Attributes\Url;
use Livewire\Component;

class CategoryDetail extends Component
{
    use InteractsWithApiPagination;

    private const DEFAULT_PER_PAGE = 9;

    private const MAX_PER_PAGE = 50;

    public string $slug;

    #[Url(as: 'per_page')]
    public int $perPage = self::DEFAULT_PER_PAGE;

    public array $category = [];

    public array $posts = [];

    public string $errorMessage = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->loadCategory();
    }

    public function loadCategory(): void
    {
        $response = ApiService::get('categories/'.$this->slug, [
            'page' => $this->page,
            'per_page' => $this->resolvedPerPage(),
        ]);

        if (! ApiService::isOk($response) || ! is_array($response['category'] ?? null)) {
            $this->category = [];
            $this->posts = [];
            $this->hydratePagination([]);
            $this->errorMessage = $this->failureMessage($response);

            return;
        }

        $postsPayload = is_array($response['posts'] ?? null) ? $response['posts'] : [];
        $posts = is_array($postsPayload['data'] ?? null) ? $postsPayload['data'] : [];

        $this->category = $response['category'];
        $this->posts = array_values(array_filter(
            $posts,
            fn ($post) => is_array($post) && filled($post['slug'] ?? null),
        ));
        $this->hydratePagination($postsPayload);
        $this->errorMessage = '';
    }

    public function onApiPageChanged(): void
    {
        $this->loadCategory();
    }

    public function render()
    {
        return view('livewire.category-detail', [
            'pagination' => $this->pagination,
        ])->layout('components.layouts.app');
    }

    private function resolvedPerPage(): int
    {
        return max(1, min(self::MAX_PER_PAGE, $this->perPage));
    }

    private function failureMessage(array $response): string
    {
        return ($response['status'] ?? null) === 404 || ApiService::isOk($response)
            ? 'Kategori bulunamadı veya şu anda görüntülenemiyor.'
            : 'Kategori şu anda yüklenemedi. Lütfen daha sonra tekrar deneyin.';
    }
}
