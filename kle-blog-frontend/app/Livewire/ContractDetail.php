<?php

namespace App\Livewire;

use App\Services\ApiService;
use Livewire\Component;

class ContractDetail extends Component
{
    public string $slug;
    public array $contract = [];

    public function mount(string $slug)
    {
        $this->slug = $slug;
        $response = ApiService::get('contracts/' . $slug);
        $this->contract = $response['data'] ?? [];
    }

    public function render()
    {
        return view('livewire.contract-detail')->layout('components.layouts.app');
    }
}