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

    public function mount()
    {
        $userResponse = ApiService::get('me');
        $userData = $userResponse['data'] ?? session('user') ?? session('user_data') ?? [];

        $this->name = $userData['name'] ?? '';
        $this->email = $userData['email'] ?? '';
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email',
        ]);

        try {
            $response = ApiService::put('profile', [
                'name' => $this->name,
                'email' => $this->email,
            ]);

            if (isset($response['data'])) {
                session(['user' => $response['data']]);
                $this->successMessage = 'Profil bilgileriniz başarıyla güncellendi.';
                $this->errorMessage = '';
            } else {
                $this->errorMessage = $response['message'] ?? 'Güncelleme yapılamadı.';
                $this->successMessage = '';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Bir hata oluştu.';
            $this->successMessage = '';
        }
    }

    public function deletePost(int $postId)
    {
        try {
            $response = ApiService::delete('posts/'.$postId);

            if (isset($response['message'])) {
                $this->successMessage = 'Yazı başarıyla silindi.';
                $this->errorMessage = '';
            } else {
                $this->errorMessage = 'Yazı silinirken bir hata oluştu.';
                $this->successMessage = '';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Yazı silinemedi.';
            $this->successMessage = '';
        }
    }

    public function render()
    {
        $response = ApiService::get('my-posts', ['page' => $this->getPage()]);
        $myPosts = $response['data'] ?? [];

        return view('livewire.dashboard', [
            'myPosts' => $myPosts,
        ])->layout('components.layouts.app');
    }
}
