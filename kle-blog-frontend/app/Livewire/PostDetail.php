<?php

namespace App\Livewire;

use App\Services\ApiService;
use Livewire\Component;

class PostDetail extends Component
{
    public $post;
    public $slug;

    // Form modelleri
    public $name = '';
    public $content = '';

    public $successMessage = '';

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->loadPost();
    }

    public function loadPost()
    {
        $response = ApiService::get('posts');
        $allPosts = $response['data'] ?? ($response ?? []);

        $rawPost = collect($allPosts)->firstWhere('slug', $this->slug);

        if (!$rawPost) {
            abort(404);
        }

        if (isset($rawPost['comments']) && is_array($rawPost['comments'])) {
            foreach ($rawPost['comments'] as $key => $comment) {
                $contentStr = $comment['content'] ?? '';
                $commenterName = $comment['name'] ?? 'Anonim';
                $commentText = $contentStr;

                if (str_contains($contentStr, ':')) {
                    $parts = explode(':', $contentStr, 2);
                    $commenterName = trim($parts[0]);
                    $commentText = trim($parts[1]);
                }

                $canDelete = false;
                if (session()->has('user_token') && session()->has('user_data')) {
                    $currentUser = session('user_data');
                    $currentUserName = trim($currentUser['name'] ?? '');
                    $currentUserRole = trim($currentUser['role'] ?? '');

                    if ($currentUserName === $commenterName || $currentUserRole === 'admin') {
                        $canDelete = true;
                    }
                }

                $rawPost['comments'][$key]['display_name'] = $commenterName;
                $rawPost['comments'][$key]['display_text'] = $commentText;
                $rawPost['comments'][$key]['can_delete'] = $canDelete;
            }
        }

        $this->post = $rawPost;
    }

    public function saveComment()
    {
       
        $token = session('user_token');
        $userData = session('user_data');

        if (!$token) {
            $this->addError('api_error', 'Yorum yapabilmek için lütfen giriş yapın.');
            return;
        }

        $this->validate([
            'content' => 'required|string|min:3|max:1000',
        ], [
            'content.required' => 'Lütfen bir yorum yazın.',
            'content.min'      => 'Yorumunuz en az 3 karakter olmalıdır.',
        ]);

        $userName = $userData['name'] ?? 'Kullanıcı';
        $fullContent = $userName . ': ' . $this->content;

        try {
        
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->post('http://kle-blog-backend-app:8000/api/comments', [
                    'post_id' => $this->post['id'],
                    'name'    => $userName,
                    'content' => $fullContent,
                ])->json();
        } catch (\Exception $e) {
            $response = null;
        }

     
        session()->put('user_token', $token);
        session()->put('user_data', $userData);
        session()->save();

        // 4. Sonuç Kontrolü
        if ($response && (isset($response['id']) || (isset($response['success']) && $response['success']) || isset($response['data']))) {
            $this->successMessage = 'Yorumunuz başarıyla gönderildi! Admin onayladıktan sonra sitede yayınlanacaktır.';
            $this->reset(['content']);
            $this->loadPost();
        } else {
         
            $this->addError('api_error', 'Yorum gönderildi veya onaya düştü.');
            $this->reset(['content']);
            $this->loadPost();
        }
    }

    public function deleteComment($commentId)
    {
        if (!session()->has('user_token')) {
            return;
        }

        $response = \Illuminate\Support\Facades\Http::withToken(session('user_token'))
            ->delete("http://kle-blog-backend-app:8000/api/comments/{$commentId}")
            ->json();

        if (isset($response['success']) && $response['success']) {
            $this->loadPost();
        } else {
            $this->addError('api_error', 'Yorum silinirken bir hata oluştu.');
        }
    }

    public function render()
    {
        return view('livewire.post-detail')->layout('components.layouts.app');
    }
}