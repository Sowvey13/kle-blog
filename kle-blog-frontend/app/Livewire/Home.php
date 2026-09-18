<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithApiPagination;
use App\Services\ApiService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Home extends Component
{
    use InteractsWithApiPagination;

    public string $search = '';

    #[Url]
    public $category_id = null;

    public array $posts = [];

    public array $categories = [];

    public function mount(): void
    {
        if (request()->has('category_id')) {
            $this->category_id = request()->query('category_id');
        }

        $this->loadCategories();
        $this->loadPosts();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->loadPosts();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
        $this->loadPosts();
    }

    public function loadCategories(): void
    {
        $response = ApiService::get('categories');

        if (ApiService::isOk($response)) {
            $this->categories = $response['data'] ?? [];
        }
    }

    public function loadPosts(): void
    {
        $queryParams = [
            'page' => $this->page,
            'per_page' => 9,
        ];

        if ($this->search !== '') {
            $queryParams['search'] = $this->search;
        }

        if (! is_null($this->category_id) && $this->category_id !== '') {
            $queryParams['category_id'] = (int) $this->category_id;
        }

        $response = ApiService::get('posts', $queryParams);

        if (ApiService::isOk($response)) {
            $this->posts = $response['data'] ?? [];
            $this->hydratePagination($response);
        } else {
            $this->posts = [];
            $this->hydratePagination([]);
        }
    }

    public function onApiPageChanged(): void
    {
        $this->loadPosts();
    }

    public function render()
    {
        return view('livewire.home', [
            'posts' => $this->posts,
            'categories' => $this->categories,
            'selectedCategoryId' => $this->category_id,
            'pagination' => $this->pagination,
        ])->layout('components.layouts.app');
    }
}
