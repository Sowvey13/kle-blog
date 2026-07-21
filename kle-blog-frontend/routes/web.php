<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Home;
use App\Livewire\PostDetail;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\AuthRequired;
use App\Livewire\CreatePost;

Route::get('/', Home::class)->name('home');
Route::get('/posts/create', CreatePost::class)->name('posts.create');
Route::get('/posts/{slug}', PostDetail::class)->name('posts.show');

Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');
Route::get('/auth-required', AuthRequired::class)->name('auth.required');

Route::get('/logout', function () {
    session()->forget(['user_token', 'user_data']);
    return redirect()->route('home');
})->name('logout');