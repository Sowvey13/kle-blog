<?php

namespace App\Livewire\Auth;

use App\Services\ApiService;
use Livewire\Component;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $errorMessage = '';

    public function login()
    {
        $this->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required'    => 'E-posta adresi zorunludur.',
            'email.email'       => 'Geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Şifre zorunludur.',
            'password.min'      => 'Şifre en az 6 karakter olmalıdır.',
        ]);

        
        $response = ApiService::post('login', [
            'email'    => $this->email,
            'password' => $this->password,
        ]);

        
        if (isset($response['access_token'])) {
            
            
            session([
                'user_token' => $response['access_token'],
                'user_data'  => $response['user']
            ]);

           
            return redirect()->route('home');
        } else {
            
            $this->errorMessage = $response['message'] ?? 'Giriş bilgileri hatalı, lütfen tekrar deneyin.';
        }
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.app');
    }
}