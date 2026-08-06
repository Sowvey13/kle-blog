<?php

namespace App\Livewire\Auth;

use App\Services\ApiService;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $errorMessage = '';

    public string $successMessage = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Adınız soyadınız alanı zorunludur.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Şifre alanı zorunludur.',
            'password.min' => 'Şifreniz en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifreler birbiriyle eşleşmiyor.',
        ];
    }

    public function register()
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        $this->validate();

        $response = ApiService::post('register', [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
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

        if (isset($response['errors']['email']) || (isset($response['message']) && str_contains($response['message'], 'email'))) {
            $this->errorMessage = 'Bu e-posta adresi zaten kayıtlı.';

            return;
        }

        $this->errorMessage = $response['message'] ?? 'Kayıt yapılırken bir hata oluştu.';
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('components.layouts.app');
    }
}
