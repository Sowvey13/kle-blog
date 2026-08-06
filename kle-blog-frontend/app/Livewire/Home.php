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

    public function mount()
    {
        if (request()->has('category_id')) {
            $this->category_id = request()->query('category_id');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
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

        $postsResponse = ApiService::get('posts', $queryParams);

        $posts = $postsResponse['data'] ?? [];
        $pagination = [
            'current_page' => $postsResponse['meta']['current_page'] ?? ($postsResponse['current_page'] ?? 1),
            'last_page' => $postsResponse['meta']['last_page'] ?? ($postsResponse['last_page'] ?? 1),
            'total' => $postsResponse['meta']['total'] ?? ($postsResponse['total'] ?? 0),
        ];

        $categoriesResponse = ApiService::get('categories');
        $categories = $categoriesResponse['data'] ?? ($categoriesResponse ?? []);

        return view('livewire.home', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategoryId' => $this->category_id,
            'pagination' => $pagination,
        ])->layout('components.layouts.app');
    }
}
