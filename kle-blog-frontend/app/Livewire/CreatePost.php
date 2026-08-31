<?php

namespace App\Livewire;

use App\Services\ApiService;
use Livewire\Component;

class CreatePost extends Component
{
    public string $title = '';

    public string $category_id = '';

    public string $content = '';

    public string $newCategoryName = '';

    public bool $showCategoryForm = false;

    public string $categorySuccessMessage = '';

    public array $categories = [];

    public function mount()
    {
        if (! session()->has('user_token')) {
            return redirect()->route('auth.required');
        }

        $categoriesResponse = ApiService::get('categories');
        $this->categories = $categoriesResponse['data'] ?? ($categoriesResponse ?? []);
    }

    public function saveCategory()
    {
        $userRole = session('user.role') ?? session('user_data.role');

        if ($userRole !== 'admin') {
            $this->addError('newCategoryName', 'Normal kullanıcılar kategori oluşturamaz.');

            return;
        }

        $this->validate([
            'newCategoryName' => 'required|string|max:255',
        ], [
            'newCategoryName.required' => 'Kategori adı yazmalısınız.',
        ]);

        $response = ApiService::post('categories', [
            'name' => $this->newCategoryName,
        ]);

        if (isset($response['data'])) {
            $createdCategory = $response['data'];
            $this->categories[] = $createdCategory;
            $this->category_id = (string) $createdCategory['id'];
            $this->reset(['newCategoryName']);
            $this->showCategoryForm = false;
            $this->categorySuccessMessage = 'Kategori başarıyla eklendi ve seçildi!';
        } else {
            $this->addError('newCategoryName', $response['message'] ?? 'Kategori eklenirken bir hata oluştu.');
        }
    }

    public function toggleCategoryForm()
    {
        $this->showCategoryForm = ! $this->showCategoryForm;
        $this->categorySuccessMessage = '';
        $this->resetErrorBag('newCategoryName');
    }

    public function savePost()
    {
        $this->validate([
            'title' => 'required|string|min:3|max:255',
            'category_id' => 'required',
            'content' => 'required|string|min:10',
        ], [
            'title.required' => 'Lütfen bir başlık girin.',
            'category_id.required' => 'Lütfen bir kategori seçin.',
            'content.required' => 'Yazı içeriği boş olamaz.',
            'content.min' => 'Yazı içeriği en az 10 karakter olmalıdır.',
        ]);

        $response = ApiService::post('posts', [
            'title' => $this->title,
            'category_id' => (int) $this->category_id,
            'content' => $this->content,
        ]);

        if (isset($response['data']) || (isset($response['message']) && ! isset($response['errors']))) {
            session()->flash('success', $response['message'] ?? 'Yazınız oluşturuldu ve onay için admin onayına gönderildi.');

            return redirect()->route('home');
        }

        $this->addError('api_error', $response['message'] ?? 'Yazı paylaşılırken bir hata oluştu.');
    }

    public function render()
    {
        return view('livewire.create-post', [
            'categories' => $this->categories,
        ])->layout('components.layouts.app');
    }
}
