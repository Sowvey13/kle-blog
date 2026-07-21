<?php

namespace App\Livewire\Auth;

use App\Services\ApiService;
use Livewire\Component;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $errorMessage = '';

    public function register()
    {
        $this->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required'     => 'İsim alanı zorunludur.',
            'email.required'    => 'E-posta adresi zorunludur.',
            'email.email'       => 'Geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Şifre zorunludur.',
            'password.min'      => 'Şifre en az 6 karakter olmalıdır.',
            'password.confirmed'=> 'Şifreler birbiriyle eşleşmiyor.',
        ]);

       
        $response = ApiService::post('register', [
            'name'                  => $this->name,
            'email'                 => $this->email,
            'password'              => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        if (isset($response['success']) && $response['success']) {
            
            session([
                'user_token' => $response['access_token'], 
                'user_data'  => $response['user']
            ]);

            return redirect()->route('home');
        } else {
            $this->errorMessage = $response['message'] ?? 'Kayıt işlemi sırasında bir hata oluştu.';
        }
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('components.layouts.app');
    }
}