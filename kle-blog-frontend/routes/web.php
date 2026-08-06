<?php

use App\Livewire\Auth\AuthRequired;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\CategoryDetail;
use App\Livewire\ContractDetail;
use App\Livewire\CreatePost;
use App\Livewire\Dashboard;
use App\Livewire\Home;
use App\Livewire\PostDetail;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/posts/{slug}', PostDetail::class)->name('posts.show');
Route::get('/categories/{slug}', CategoryDetail::class)->name('categories.show');
Route::get('/contracts/{slug}', ContractDetail::class)->name('contracts.show');

Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');
Route::get('/auth-required', AuthRequired::class)->name('auth.required');

Route::post('/login-action', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $response = ApiService::post('login', [
        'email' => $request->email,
        'password' => $request->password,
    ]);

    $token = $response['token'] ?? ($response['data']['token'] ?? null);
    $user = $response['user'] ?? ($response['data']['user'] ?? null);

    if ($token) {
        session()->put('user_token', $token);
        session()->put('user', $user);
        session()->put('user_data', $user);
        session()->save();

        return redirect()->route('home');
    }

    return back()->with('errorMessage', $response['message'] ?? 'E-posta veya şifre hatalı.')->withInput();
})->name('login.action');

Route::post('/register-action', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ], [
        'name.required' => 'Adınız soyadınız alanı zorunludur.',
        'email.required' => 'E-posta adresi zorunludur.',
        'email.email' => 'Geçerli bir e-posta adresi giriniz.',
        'password.required' => 'Şifre alanı zorunludur.',
        'password.min' => 'Şifreniz en az 8 karakter olmalıdır.',
        'password.confirmed' => 'Şifreler birbiriyle eşleşmiyor.',
    ]);

    $response = ApiService::post('register', [
        'name' => $request->name,
        'email' => $request->email,
        'password' => $request->password,
        'password_confirmation' => $request->password_confirmation,
    ]);

    if (isset($response['data']) || isset($response['user']) || isset($response['token'])) {
        $token = $response['token'] ?? ($response['data']['token'] ?? null);
        $user = $response['user'] ?? ($response['data']['user'] ?? null);

        if ($token) {
            session()->put('user_token', $token);
            session()->put('user', $user);
            session()->put('user_data', $user);
            session()->save();
            return redirect()->route('home');
        }

        return redirect()->route('login')->with('successMessage', 'Kayıt başarılı! Giriş yapabilirsiniz.');
    }

    $errorMessage = $response['message'] ?? 'Kayıt yapılırken bir hata oluştu.';
    if ($errorMessage === 'The email has already been taken.') {
        $errorMessage = 'Bu e-posta adresi zaten kullanımda.';
    }

    return back()->with('errorMessage', $errorMessage)->withInput();
})->name('register.action');

Route::post('/logout', function () {
    session()->forget(['user', 'user_data', 'user_token']);
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('home');
})->name('logout');

Route::middleware(['web'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/posts-create', CreatePost::class)->name('posts.create');
});