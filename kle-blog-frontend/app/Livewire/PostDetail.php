<?php

namespace App\Livewire;

use App\Services\ApiService;
use Livewire\Component;

class PostDetail extends Component
{
    public string $slug = '';

    public string $content = '';

    public string $errorMessage = '';

    public string $successMessage = '';

    public ?array $post = null;

    public function mount(string $slug)
    {
        $this->slug = $slug;
        $this->fetchPost();
    }

    private function fetchPost()
    {
        try {
            $response = ApiService::get('posts/'.$this->slug);
            $this->post = $response['data'] ?? ($response ?? null);
        } catch (\Exception $e) {
            $this->post = null;
        }
    }

    public function saveComment()
    {
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->resetErrorBag();

        if (! session()->has('user_token')) {
            return redirect()->route('login');
        }

        $this->validate([
            'content' => 'required|string|min:3',
        ], [
            'content.required' => 'Yorum alanı boş bırakılamaz.',
            'content.min' => 'Yorum en az 3 karakter olmalıdır.',
        ]);

        try {
            $response = ApiService::get('posts/'.$this->slug);
            $currentPost = $response['data'] ?? ($response ?? null);
            $postId = $currentPost['id'] ?? null;

            if (! $postId) {
                $this->addError('api_error', 'Yorum eklenecek yazı bulunamadı.');

                return;
            }

            $res = ApiService::post('comments', [
                'post_id' => $postId,
                'content' => $this->content,
            ]);

            if (isset($res['message']) && str_contains(strtolower($res['message']), 'hata')) {
                $this->addError('api_error', $res['message']);

                return;
            }

            $this->content = '';
            $this->successMessage = 'Yorumunuz alındı, admin onayından sonra yayınlanacaktır.';

            // Session'ı tekrar sabitleyip yeniliyoruz
            session()->save();
            $this->fetchPost();
        } catch (\Exception $e) {
            $this->addError('api_error', $e->getMessage() ?: 'Yorum eklenirken bir hata oluştu.');
        }
    }

    public function deleteComment(int $commentId)
    {
        if (! session()->has('user_token')) {
            return redirect()->route('login');
        }

        try {
            ApiService::delete('comments/'.$commentId);
            $this->successMessage = 'Yorum silindi.';
            $this->fetchPost();
        } catch (\Exception $e) {
            $this->addError('api_error', 'Yorum silinirken bir yetki hatası oluştu.');
        }
    }

    public function deletePost(int $postId)
    {
        if (! session()->has('user_token')) {
            return redirect()->route('login');
        }

        try {
            ApiService::delete('posts/'.$postId);

            return redirect()->route('home');
        } catch (\Exception $e) {
            $this->addError('api_error', $e->getMessage() ?: 'Yazı silinirken yetki hatası oluştu.');
        }
    }

    public function render()
    {
        return view('livewire.post-detail')->layout('components.layouts.app');
    }
}
