<?php

namespace App\Livewire;

use App\Livewire\Concerns\ResolvesApiErrors;
use App\Services\ApiService;
use Livewire\Component;

class PostDetail extends Component
{
    use ResolvesApiErrors;

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

        if (ApiService::isOk($response) && is_array($response['data'] ?? null)) {
            $this->post = $response['data'];
            $this->errorMessage = '';

            return;
        }

        $this->post = null;
        $this->errorMessage = ($response['status'] ?? null) === 404
            ? ''
            : 'Yazı şu anda yüklenemedi. Lütfen daha sonra tekrar deneyin.';
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

        if (! ApiService::isOk($res)) {
            $this->addError('api_error', $this->apiErrorMessage($res, 'Yorum şu anda gönderilemedi. Lütfen daha sonra tekrar deneyin.'));

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

        if (ApiService::isOk($res)) {
            $this->successMessage = 'Yorum silindi.';
            $this->fetchPost();

            return;
        }

        $this->addError('api_error', $this->apiErrorMessage($res, 'Yorum şu anda silinemedi. Lütfen daha sonra tekrar deneyin.'));
    }

    public function deletePost(int $postId)
    {
        if (! session()->has('user_token')) {
            return redirect()->route('login');
        }

        $res = ApiService::delete('posts/'.$postId);

        if (ApiService::isOk($res)) {
            return redirect()->route('home');
        }

        $this->addError('api_error', $this->apiErrorMessage($res, 'Yazı şu anda silinemedi. Lütfen daha sonra tekrar deneyin.'));
    }

    public function render()
    {
        return view('livewire.post-detail')->layout('components.layouts.app');
    }
}
