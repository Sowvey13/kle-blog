<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithApiPagination;
use App\Services\ApiService;
use Livewire\Component;

class Dashboard extends Component
{
    use InteractsWithApiPagination;

    public string $name = '';

    public string $email = '';

    public string $successMessage = '';

    public string $errorMessage = '';

    public array $myPosts = [];

    public function mount(): void
    {
        $userResponse = ApiService::get('me');
        $userData = ApiService::isOk($userResponse)
            ? ($userResponse['data'] ?? [])
            : (session('user') ?? session('user_data') ?? []);

        $this->name = $userData['name'] ?? '';
        $this->email = $userData['email'] ?? '';

        $this->loadMyPosts();
    }

    public function loadMyPosts(): void
    {
        $response = ApiService::get('my-posts', [
            'page' => $this->page,
            'per_page' => 10,
        ]);

        if (ApiService::isOk($response)) {
            $this->myPosts = $response['data'] ?? [];
            $this->hydratePagination($response);
        } else {
            $this->myPosts = [];
            $this->hydratePagination([]);
        }
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email',
        ]);

        $response = ApiService::put('profile', [
            'name' => $this->name,
            'email' => $this->email,
        ]);

        if (ApiService::isOk($response) && isset($response['data'])) {
            session(['user' => $response['data']]);
            $this->successMessage = 'Profil bilgileriniz başarıyla güncellendi.';
            $this->errorMessage = '';
        } else {
            $this->errorMessage = $response['message'] ?? 'Güncelleme yapılamadı.';
            $this->successMessage = '';
        }
    }

    public function deletePost(int $postId): void
    {
        $response = ApiService::delete('posts/'.$postId);

        if (ApiService::isOk($response)) {
            $this->successMessage = 'Yazı başarıyla silindi.';
            $this->errorMessage = '';
            $this->loadMyPosts();
        } else {
            $this->errorMessage = $response['message'] ?? 'Yazı silinirken bir hata oluştu.';
            $this->successMessage = '';
        }
    }

    public function onApiPageChanged(): void
    {
        $this->loadMyPosts();
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'myPosts' => $this->myPosts,
            'pagination' => $this->pagination,
        ])->layout('components.layouts.app');
    }
}
