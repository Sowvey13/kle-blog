<?php

namespace App\Livewire;

use App\Services\ApiService;
use Livewire\Component;

class CategoryDetail extends Component
{
    public string $slug;
    public array $category = [];
    public array $posts = [];

    public function mount(string $slug)
    {
        $this->slug = $slug;
        $categoryResponse = ApiService::get('categories/' . $slug);
        
        $this->category = $categoryResponse['data'] ?? [];
        $this->posts = $categoryResponse['data']['posts'] ?? [];
    }

    public function render()
    {
        return view('livewire.category-detail')->layout('components.layouts.app');
    }
}