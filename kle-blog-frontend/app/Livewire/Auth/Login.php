<?php

namespace App\Livewire\Auth;

use App\Services\ApiService;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public string $errorMessage = '';

    public string $successMessage = '';

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Şifre alanı zorunludur.',
        ];
    }

    public function login()
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        $this->validate();

        $response = ApiService::post('login', [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        if (isset($response['error']) && $response['error'] === true) {
            if (isset($response['status']) && $response['status'] === 429) {
                $this->errorMessage = 'Çok fazla hatalı giriş denemesi yaptınız. Lütfen bir süre bekleyip tekrar deneyin.';

                return;
            }

            if (isset($response['status']) && $response['status'] === 401) {
                $this->errorMessage = 'Girdiğiniz e-posta adresi veya şifre hatalı.';

                return;
            }

            $this->errorMessage = $response['message'] ?? 'Giriş yapılamadı. Bilgilerinizi kontrol ediniz.';

            return;
        }

        $token = $response['token'] ?? ($response['data']['token'] ?? null);
        $user = $response['user'] ?? ($response['data']['user'] ?? null);

        if ($token) {
            session()->put('user_token', $token);
            session()->put('user', $user);
            session()->put('user_data', $user);
            session()->save();

            $this->successMessage = 'Giriş başarılı! Yönlendiriliyorsunuz...';

            return redirect()->route('home');
        }

        $this->errorMessage = 'Girdiğiniz e-posta adresi veya şifre hatalı.';
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.app');
    }
}
