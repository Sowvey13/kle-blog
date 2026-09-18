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
        $this->categories = ApiService::isOk($categoriesResponse)
            ? ($categoriesResponse['data'] ?? [])
            : [];
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

        if (ApiService::isOk($response) && isset($response['data']['id'])) {
            $createdCategory = $response['data'];
            $this->categories[] = $createdCategory;
            $this->category_id = (string) $createdCategory['id'];
            $this->reset(['newCategoryName']);
            $this->showCategoryForm = false;
            $this->categorySuccessMessage = 'Kategori başarıyla eklendi ve seçildi!';

            return;
        }

        $this->addError('newCategoryName', $response['message'] ?? 'Kategori eklenirken bir hata oluştu.');
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

        if (ApiService::isOk($response) && $this->hasCreatedPostPayload($response)) {
            session()->flash('success', $response['message'] ?? 'Yazınız oluşturuldu ve onay için admin onayına gönderildi.');

            return redirect()->route('home');
        }

        $this->addError('api_error', $this->postFailureMessage($response));
    }

    public function render()
    {
        return view('livewire.create-post', [
            'categories' => $this->categories,
        ])->layout('components.layouts.app');
    }

    private function hasCreatedPostPayload(array $response): bool
    {
        $data = $response['data'] ?? null;

        return is_array($data) && isset($data['id'], $data['title']);
    }

    private function postFailureMessage(array $response): string
    {
        $status = (int) ($response['status'] ?? 0);

        return match ($status) {
            401 => 'Oturumunuz sona ermiş olabilir. Lütfen tekrar giriş yapın.',
            403 => 'Bu işlemi gerçekleştirme yetkiniz yok.',
            422 => $this->firstValidationError($response) ?? ($response['message'] ?? 'Girdiğiniz bilgileri kontrol edin.'),
            500 => 'Yazı şu anda kaydedilemedi. Lütfen daha sonra tekrar deneyin.',
            default => $response['message'] ?? 'Yazı paylaşılırken bir hata oluştu.',
        };
    }

    private function firstValidationError(array $response): ?string
    {
        $errors = $response['errors'] ?? [];

        if (! is_array($errors) || $errors === []) {
            return null;
        }

        $first = collect($errors)->flatten()->first();

        return is_string($first) ? $first : null;
    }
}
