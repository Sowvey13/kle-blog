<?php

namespace App\Livewire;

use App\Services\ApiService;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public string $name = '';

    public string $email = '';

    public string $successMessage = '';

    public string $errorMessage = '';

    public array $myPosts = [];

    public function mount()
    {
        $userResponse = ApiService::get('me');
        $userData = (! isset($userResponse['error']) ? ($userResponse['data'] ?? $userResponse) : null) ?? session('user') ?? session('user_data') ?? [];

        $this->name = $userData['name'] ?? '';
        $this->email = $userData['email'] ?? '';

        $this->loadMyPosts();
    }

    public function loadMyPosts()
    {
        $response = ApiService::get('my-posts', ['page' => $this->getPage()]);
        if (! isset($response['error'])) {
            $this->myPosts = $response['data'] ?? [];
        } else {
            $this->myPosts = [];
        }
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email',
        ]);

        $response = ApiService::put('profile', [
            'name' => $this->name,
            'email' => $this->email,
        ]);

        if (! isset($response['error']) && isset($response['data'])) {
            session(['user' => $response['data']]);
            $this->successMessage = 'Profil bilgileriniz başarıyla güncellendi.';
            $this->errorMessage = '';
        } else {
            $this->errorMessage = $response['message'] ?? 'Güncelleme yapılamadı.';
            $this->successMessage = '';
        }
    }

    public function deletePost(int $postId)
    {
        $response = ApiService::delete('posts/'.$postId);

        if (! isset($response['error'])) {
            $this->successMessage = 'Yazı başarıyla silindi.';
            $this->errorMessage = '';
            $this->loadMyPosts();
        } else {
            $this->errorMessage = $response['message'] ?? 'Yazı silinirken bir hata oluştu.';
            $this->successMessage = '';
        }
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'myPosts' => $this->myPosts,
        ])->layout('components.layouts.app');
    }
}
