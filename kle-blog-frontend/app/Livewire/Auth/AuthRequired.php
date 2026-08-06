<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class AuthRequired extends Component
{
    public function render()
    {
        return view('livewire.auth.auth-required')->layout('components.layouts.app');
    }
}
