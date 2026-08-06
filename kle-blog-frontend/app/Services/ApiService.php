<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiService
{
    private static function getBaseUrl(): string
    {
        return config('services.backend.url', 'http://kle-blog-backend-app:8000') . '/api';
    }

    private static function getHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        if (session()->has('user_token') && session('user_token')) {
            $headers['Authorization'] = 'Bearer ' . session('user_token');
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

    public static function get(string $endpoint, array $query = [])
    {
        $url = self::getBaseUrl() . '/' . self::formatEndpoint($endpoint);

        try {
            $response = Http::withHeaders(self::getHeaders())->get($url, $query);

            if (!$response->successful() && config('app.env') === 'local') {
                $fallbackUrl = 'http://localhost:8000/api/' . self::formatEndpoint($endpoint);
                $response = Http::withHeaders(self::getHeaders())->get($fallbackUrl, $query);
            }

            return $response->json();
        } catch (\Exception $e) {
            try {
                $fallbackUrl = 'http://localhost:8000/api/' . self::formatEndpoint($endpoint);
                return Http::withHeaders(self::getHeaders())->get($fallbackUrl, $query)->json();
            } catch (\Exception $ex) {
                return null;
            }
        }
    }

    public static function post(string $endpoint, array $data = [])
    {
        $url = self::getBaseUrl() . '/' . self::formatEndpoint($endpoint);

        try {
            $response = Http::withHeaders(self::getHeaders())->post($url, $data);

            if (!$response->successful() && config('app.env') === 'local') {
                $fallbackUrl = 'http://localhost:8000/api/' . self::formatEndpoint($endpoint);
                $response = Http::withHeaders(self::getHeaders())->post($fallbackUrl, $data);
            }

            return $response->json();
        } catch (\Exception $e) {
            try {
                $fallbackUrl = 'http://localhost:8000/api/' . self::formatEndpoint($endpoint);
                return Http::withHeaders(self::getHeaders())->post($fallbackUrl, $data)->json();
            } catch (\Exception $ex) {
                return null;
            }
        }
    }

    public static function put(string $endpoint, array $data = [])
    {
        $url = self::getBaseUrl() . '/' . self::formatEndpoint($endpoint);

        try {
            $response = Http::withHeaders(self::getHeaders())->put($url, $data);

            if (!$response->successful() && config('app.env') === 'local') {
                $fallbackUrl = 'http://localhost:8000/api/' . self::formatEndpoint($endpoint);
                $response = Http::withHeaders(self::getHeaders())->put($fallbackUrl, $data);
            }

            return $response->json();
        } catch (\Exception $e) {
            try {
                $fallbackUrl = 'http://localhost:8000/api/' . self::formatEndpoint($endpoint);
                return Http::withHeaders(self::getHeaders())->put($fallbackUrl, $data)->json();
            } catch (\Exception $ex) {
                return null;
            }
        }
    }

    public static function delete(string $endpoint)
    {
        $url = self::getBaseUrl() . '/' . self::formatEndpoint($endpoint);

        try {
            $response = Http::withHeaders(self::getHeaders())->delete($url);

            if (!$response->successful() && config('app.env') === 'local') {
                $fallbackUrl = 'http://localhost:8000/api/' . self::formatEndpoint($endpoint);
                $response = Http::withHeaders(self::getHeaders())->delete($fallbackUrl);
            }

            return $response->json();
        } catch (\Exception $e) {
            try {
                $fallbackUrl = 'http://localhost:8000/api/' . self::formatEndpoint($endpoint);
                return Http::withHeaders(self::getHeaders())->delete($fallbackUrl)->json();
            } catch (\Exception $ex) {
                return null;
            }
        }
    }
}