<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Services\ApiService;

class CreatePost extends Component
{
    public $title = '';
    public $category_id = '';
    public $content = '';
    
    public $newCategoryName = '';
    public $showCategoryForm = false;
    public $categorySuccessMessage = '';

    public function mount()
    {
        if (!session()->has('user_token')) {
            return redirect()->route('auth.required');
        }
    }

    public function saveCategory()
    {
        $this->validate([
            'newCategoryName' => 'required|string|max:255',
        ], [
            'newCategoryName.required' => 'Kategori adı yazmalısınız.',
        ]);

        $response = Http::withToken(session('user_token'))
            ->post('http://kle-blog-backend-app:8000/api/categories', [
                'name' => $this->newCategoryName,
            ]);

        if ($response->successful()) {
            $result = $response->json();
            
            $this->category_id = $result['category']['id'] ?? '';
            $this->reset(['newCategoryName']);
            $this->showCategoryForm = false;
            
            $this->categorySuccessMessage = 'Kategori başarıyla eklendi ve seçildi!';
        } else {
            $errorData = $response->json();
            $this->addError('newCategoryName', $errorData['message'] ?? 'Kategori eklenirken bir hata oluştu.');
        }
    }

    public function toggleCategoryForm()
    {
        $this->showCategoryForm = !$this->showCategoryForm;
        $this->categorySuccessMessage = '';
        $this->resetErrorBag('newCategoryName');
    }

    public function savePost()
    {
        $this->validate([
            'title'       => 'required|string|min:3|max:255',
            'category_id' => 'required',
            'content'     => 'required|string|min:10',
        ], [
            'title.required'       => 'Lütfen bir başlık girin.',
            'category_id.required' => 'Lütfen bir kategori seçin.',
            'content.required'     => 'Yazı içeriği boş olamaz.',
            'content.min'          => 'Yazı içeriği en az 10 karakter olmalıdır.'
        ]);

        $response = Http::withToken(session('user_token'))
            ->post('http://kle-blog-backend-app:8000/api/posts', [
                'title'       => $this->title,
                'category_id' => $this->category_id,
                'content'     => $this->content,
            ]);

        if ($response->successful()) {
            return redirect()->route('home');
        } else {
            $this->addError('api_error', 'Yazı paylaşılırken bir hata oluştu: ' . $response->body());
        }
    }

    public function render()
    {
        $categoriesData = [];
        try {
            $response = Http::get('http://kle-blog-backend-app:8000/api/categories');
            if ($response->successful()) {
                $categoriesData = $response->json()['data'] ?? ($response->json() ?? []);
            }
        } catch (\Exception $e) {
            $categoriesData = [];
        }

        return view('livewire.create-post', [
            'categories' => is_array($categoriesData) ? $categoriesData : []
        ])->layout('components.layouts.app');
    }
}