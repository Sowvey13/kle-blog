<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogApiController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/posts', [BlogApiController::class, 'getPosts']);
Route::get('/categories', [BlogApiController::class, 'getCategories']);
Route::get('/contracts', [BlogApiController::class, 'getContracts']);


Route::middleware('auth:sanctum')->group(function () {
    // Oturumu Kapatma
    Route::post('/logout', [AuthController::class, 'logout']);
    
  
    Route::post('/categories', [BlogApiController::class, 'storeCategory']);
    
    
    Route::post('/comments', [BlogApiController::class, 'storeComment']);
    Route::delete('/comments/{id}', [BlogApiController::class, 'deleteComment']);

  
    Route::post('/posts', [BlogApiController::class, 'storePost']);
    Route::delete('/posts/{id}', [BlogApiController::class, 'deletePost']);
});