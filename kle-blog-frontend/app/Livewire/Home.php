<?php

namespace App\Livewire;

use App\Services\ApiService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Home extends Component
{
    use WithPagination;

    public string $search = '';

    #[Url]
    public $category_id = null;

    public array $posts = [];

    public array $categories = [];

    public array $pagination = [
        'current_page' => 1,
        'last_page' => 1,
        'total' => 0,
    ];

    public function mount()
    {
        if (request()->has('category_id')) {
            $this->category_id = request()->query('category_id');
        }

        $this->loadCategories();
        $this->loadPosts();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadPosts();
    }

    public function updatedCategoryId()
    {
        $this->resetPage();
        $this->loadPosts();
    }

    public function loadCategories()
    {
        $response = ApiService::get('categories');
        if (! isset($response['error'])) {
            $this->categories = $response['data'] ?? ($response ?? []);
        }
    }

    public function loadPosts()
    {
        $queryParams = [
            'page' => $this->getPage(),
            'per_page' => 9,
        ];

        if (! empty($this->search)) {
            $queryParams['search'] = $this->search;
        }

        if (! is_null($this->category_id) && $this->category_id !== '') {
            $queryParams['category_id'] = (int) $this->category_id;
        }

        $response = ApiService::get('posts', $queryParams);

        if (! isset($response['error'])) {
            $this->posts = $response['data'] ?? [];
            $this->pagination = [
                'current_page' => $response['meta']['current_page'] ?? ($response['current_page'] ?? 1),
                'last_page' => $response['meta']['last_page'] ?? ($response['last_page'] ?? 1),
                'total' => $response['meta']['total'] ?? ($response['total'] ?? 0),
            ];
        } else {
            $this->posts = [];
        }
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
