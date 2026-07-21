<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiService
{
    
    public static function get(string $endpoint, array $query = [])
    {
        $baseUrl = env('NEXT_PUBLIC_API_URL', 'http://localhost:8000/api');
        
        
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->get("{$baseUrl}/{$endpoint}", $query);

        return $response->json();
    }

    
    public static function post(string $endpoint, array $data = [])
    {
        $baseUrl = env('NEXT_PUBLIC_API_URL', 'http://localhost:8000/api');

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post("{$baseUrl}/{$endpoint}", $data);

        return $response->json();
    }
}