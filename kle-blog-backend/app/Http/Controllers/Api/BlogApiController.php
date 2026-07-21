<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Contract;
use App\Models\Comment;
use Illuminate\Http\Request;

class BlogApiController extends Controller
{
    /**
     * Tüm aktif blog yazılarını ilişkileri ve onaylı yorumları ile birlikte getirir.
     */
    public function getPosts()
    {
        $posts = Post::with(['category', 'user', 'comments'])
            ->where('is_active', true)
            ->latest()
            ->get();

        return response()->json($posts);
    }

    
    public function getCategories()
{
   
    $categories = Category::all();

    return response()->json([
        'status' => 'success',
        'data'   => $categories
    ], 200);
}

    
    public function getContracts()
    {
        $contracts = Contract::where('is_active', true)->get();
        return response()->json($contracts);
    }

    
    public function storeComment(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'name'    => 'required|string|max:255', 
            'content' => 'required|string|min:3|max:1000', 
        ]);

        $comment = Comment::create([
            'post_id'     => $request->post_id,
            'user_id'     => null, 
            'content'     => $request->input('content'), 
            'is_approved' => false, 
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Yorumunuz başarıyla eklendi ve onay bekliyor.',
            'comment' => $comment
        ], 201);
    }

   
    public function storePost(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
        ]);

        $slug = \Illuminate\Support\Str::slug($request->title) . '-' . rand(100, 999);

        $post = Post::create([
            'user_id'     => $request->user() ? $request->user()->id : 1, 
            'category_id' => $request->category_id,
            'title'       => $request->title,
            'slug'        => $slug,
            'content'     => $request->input('content'),
            'is_active'   => true,
            'views'       => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Yeni blog yazısı başarıyla eklendi!',
            'post'    => $post
        ], 201);
    }

    
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ], [
            'name.required' => 'Kategori adı boş olamaz.',
            'name.unique'   => 'Bu kategori zaten mevcut.',
        ]);

        $slug = \Illuminate\Support\Str::slug($request->name);

        $category = Category::create([
            'name'      => $request->name,
            'slug'      => $slug,
            'is_active' => true,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Kategori başarıyla oluşturuldu!',
            'category' => $category
        ], 201);
    }

    
    public function deletePost($id, Request $request)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Yazı bulunamadı.'], 404);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Yetkisiz işlem.'], 401);
        }

        // Giriş yapan kişi yazının sahibi mi VEYA sistem admini mi?
        if ($post->user_id == $user->id || $user->role === 'admin') {
            $post->delete();
            return response()->json(['success' => true, 'message' => 'Yazı başarıyla silindi.']);
        }

        return response()->json(['message' => 'Bu yazıyı silmeye yetkiniz yok.'], 403);
    }

    public function deleteComment($id, Request $request)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Yorum bulunamadı.'], 404);
        }

        $user = $request->user(); 

        $contentStr = $comment->content ?? '';
        $commenterName = '';
        
        if (str_contains($contentStr, ':')) {
            $parts = explode(':', $contentStr, 2);
            $commenterName = trim($parts[0]);
        }

        if (trim($user->name) === $commenterName || $user->role === 'admin') {
            $comment->delete();
            return response()->json(['success' => true, 'message' => 'Yorum başarıyla silindi.']);
        }

        return response()->json(['message' => 'Bu yorumu silmeye yetkiniz yok.'], 403);
    }
}