<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Services\ApiService;

class Home extends Component
{
    public $search = '';
    public $selectedCategoryId = null;

   
    public function selectCategory($categoryId)
    {
        $this->selectedCategoryId = $categoryId;
    }

   
    public function deletePost($postId)
    {
        if (!session()->has('user_token')) {
            return;
        }

        $response = Http::withToken(session('user_token'))
            ->delete("http://kle-blog-backend-app:8000/api/posts/{$postId}");

        if ($response->successful()) {
          
            $this->dispatch('$refresh');
        } else {
            $this->addError('api_error', 'Yazı silinirken bir hata oluştu veya yetkiniz yok.');
        }
    }

    public function render()
    {
   
        $response = Http::get('http://kle-blog-backend-app:8000/api/categories');
        $categoriesData = $response->json();

        $categories = [];
        if ($response->successful() && is_array($categoriesData)) {
          
            $categories = $categoriesData['data'] ?? $categoriesData;
        }

      
        $postsResponse = ApiService::get('posts');
        $allPosts = $postsResponse['data'] ?? ($postsResponse ?? []);

      
        $processedPosts = collect($allPosts)->map(function ($post) {
            $canDeletePost = false;

            if (session()->has('user_token') && session()->has('user_data')) {
                $currentUser = session('user_data');
                
              
                $postUserId = $post['user_id'] ?? ($post['user']['id'] ?? null);
                
             
                $currentUserId = $currentUser['id'] ?? null;
                $currentUserRole = $currentUser['role'] ?? null;
                
            
                if (($currentUserId == $postUserId && !is_null($currentUserId)) || $currentUserRole === 'admin') {
                    $canDeletePost = true;
                }
            }

            $post['can_delete_post'] = $canDeletePost;
            return $post;
        });

      
        if (!empty($this->search)) {
            $processedPosts = $processedPosts->filter(function ($post) {
                return str_contains(mb_strtolower($post['title'], 'UTF-8'), mb_strtolower($this->search, 'UTF-8')) ||
                       str_contains(mb_strtolower($post['content'], 'UTF-8'), mb_strtolower($this->search, 'UTF-8'));
            });
        }

       
        if (!is_null($this->selectedCategoryId)) {
            $processedPosts = $processedPosts->filter(function ($post) {
                return ($post['category_id'] ?? ($post['category']['id'] ?? null)) == $this->selectedCategoryId;
            });
        }

        return view('livewire.home', [
            'categories' => $categories,
            'posts'      => $processedPosts
        ])->layout('components.layouts.app');
    }
}