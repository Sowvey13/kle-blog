<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiService
{
    private static function getBaseUrl(): string
    {
        return rtrim(config('services.backend.url', 'http://kle-blog-backend-app:8000'), '/').'/api';
    }

    private static function getHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        if (session()->has('user_token') && filled(session('user_token'))) {
            $headers['Authorization'] = 'Bearer '.session('user_token');
        }

        return $headers;
    }

    private static function formatEndpoint(string $endpoint): string
    {
        $endpoint = ltrim($endpoint, '/');
        if (str_starts_with($endpoint, 'api/')) {
            $endpoint = substr($endpoint, 4);
        }

        return $endpoint;
    }

    public static function get(string $endpoint, array $query = []): ?array
    {
        $url = self::getBaseUrl().'/'.self::formatEndpoint($endpoint);

        try {
            $response = Http::withHeaders(self::getHeaders())->get($url, $query);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'error' => true,
                'status' => $response->status(),
                'message' => $response->json('message') ?? 'Bir hata oluştu.',
                'errors' => $response->json('errors') ?? [],
            ];
        } catch (\Throwable $e) {
            return [
                'error' => true,
                'status' => 500,
                'message' => 'Backend bağlantı hatası: '.$e->getMessage(),
            ];
        }
    }

    public static function post(string $endpoint, array $data = []): ?array
    {
        $url = self::getBaseUrl().'/'.self::formatEndpoint($endpoint);

        try {
            $response = Http::withHeaders(self::getHeaders())->post($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'error' => true,
                'status' => $response->status(),
                'message' => $response->json('message') ?? 'İşlem başarısız oldu.',
                'errors' => $response->json('errors') ?? [],
            ];
        } catch (\Throwable $e) {
            return [
                'error' => true,
                'status' => 500,
                'message' => 'Backend bağlantı hatası: '.$e->getMessage(),
            ];
        }
    }

    public static function put(string $endpoint, array $data = []): ?array
    {
        $url = self::getBaseUrl().'/'.self::formatEndpoint($endpoint);

        try {
            $response = Http::withHeaders(self::getHeaders())->put($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'error' => true,
                'status' => $response->status(),
                'message' => $response->json('message') ?? 'Güncelleme başarısız oldu.',
                'errors' => $response->json('errors') ?? [],
            ];
        } catch (\Throwable $e) {
            return [
                'error' => true,
                'status' => 500,
                'message' => 'Backend bağlantı hatası: '.$e->getMessage(),
            ];
        }
    }

    public static function delete(string $endpoint): ?array
    {
        $url = self::getBaseUrl().'/'.self::formatEndpoint($endpoint);

        try {
            $response = Http::withHeaders(self::getHeaders())->delete($url);

            if ($response->successful()) {
                return $response->json() ?? ['message' => 'Silindi.'];
            }

            return [
                'error' => true,
                'status' => $response->status(),
                'message' => $response->json('message') ?? 'Silme işlemi başarısız.',
            ];
        } catch (\Throwable $e) {
            return [
                'error' => true,
                'status' => 500,
                'message' => 'Backend bağlantı hatası: '.$e->getMessage(),
            ];
        }
    }
}
