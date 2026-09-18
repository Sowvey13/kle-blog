<?php

namespace App\Livewire;

use App\Services\ApiService;
use Livewire\Component;

class CategoryDetail extends Component
{
    public string $slug;

    public array $category = [];

    public array $posts = [];

    public string $errorMessage = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        $response = ApiService::get('categories/'.$slug);

        if (! ApiService::isOk($response) || empty($response['category'])) {
            $this->category = [];
            $this->posts = [];
            $this->errorMessage = 'Kategori bulunamadı veya şu anda görüntülenemiyor.';

            return;
        }

        $this->category = is_array($response['category']) ? $response['category'] : [];

        $postsPayload = $response['posts'] ?? [];
        $this->posts = is_array($postsPayload['data'] ?? null)
            ? $postsPayload['data']
            : [];
    }

    public function render()
    {
        return view('livewire.category-detail')->layout('components.layouts.app');
    }
}
