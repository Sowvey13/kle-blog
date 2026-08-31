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

    public function fetchPost()
    {
        $response = ApiService::get('posts/'.$this->slug);
        if (! isset($response['error'])) {
            $this->post = $response['data'] ?? ($response ?? null);
        } else {
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

        $postId = $this->post['id'] ?? null;

        if (! $postId) {
            $this->addError('api_error', 'Yorum eklenecek yazı bulunamadı.');

            return;
        }

        $res = ApiService::post('comments', [
            'post_id' => $postId,
            'content' => $this->content,
        ]);

        if (isset($res['error']) && $res['error']) {
            $this->addError('api_error', $res['message'] ?? 'Yorum gönderilemedi.');

            return;
        }

        $this->content = '';
        $this->successMessage = $res['message'] ?? 'Yorumunuz alındı, admin onayından sonra yayınlanacaktır.';
        $this->fetchPost();
    }

    public function deleteComment(int $commentId)
    {
        if (! session()->has('user_token')) {
            return redirect()->route('login');
        }

        $res = ApiService::delete('comments/'.$commentId);
        if (! isset($res['error'])) {
            $this->successMessage = 'Yorum silindi.';
            $this->fetchPost();
        } else {
            $this->addError('api_error', $res['message'] ?? 'Yorum silinirken yetki hatası oluştu.');
        }
    }

    public function deletePost(int $postId)
    {
        if (! session()->has('user_token')) {
            return redirect()->route('login');
        }

        $res = ApiService::delete('posts/'.$postId);
        if (! isset($res['error'])) {
            return redirect()->route('home');
        }

        $this->addError('api_error', $res['message'] ?? 'Yazı silinirken yetki hatası oluştu.');
    }

    public function render()
    {
        return view('livewire.post-detail')->layout('components.layouts.app');
    }
}
