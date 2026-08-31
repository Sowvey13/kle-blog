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
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|confirmed',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Ad Soyad alanı zorunludur.',
            'name.min' => 'Ad Soyad en az 3 karakter olmalıdır.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Şifre alanı zorunludur.',
            'password.min' => 'Şifreniz en az 6 karakterden oluşmalıdır.',
            'password.confirmed' => 'Girdiğiniz şifreler birbiriyle eşleşmiyor.',
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

        if (isset($response['error']) && $response['error'] === true) {
            // Backend validasyon hatalarını (örn: bu e-posta zaten kayıtlı) yakalama
            if (isset($response['errors']) && is_array($response['errors'])) {
                $firstError = collect($response['errors'])->flatten()->first();
                if ($firstError) {
                    $this->errorMessage = $firstError;

                    return;
                }
            }

            if (isset($response['status']) && $response['status'] === 422) {
                $this->errorMessage = 'Bu e-posta adresi zaten kullanımda veya geçersiz bilgi girdiniz.';

                return;
            }

            $this->errorMessage = $response['message'] ?? 'Kayıt işlemi sırasında bir hata oluştu.';

            return;
        }

        $token = $response['token'] ?? ($response['data']['token'] ?? null);
        $user = $response['user'] ?? ($response['data']['user'] ?? null);

        if ($token) {
            session()->put('user_token', $token);
            session()->put('user', $user);
            session()->put('user_data', $user);
            session()->save();

            return redirect()->route('home');
        }

        $this->successMessage = 'Kayıt başarılı! Giriş yapabilirsiniz.';

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('components.layouts.app');
    }
}
