<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApiService
{
    private const TIMEOUT_SECONDS = 8;

    private const CONNECT_TIMEOUT_SECONDS = 5;

    private const GENERIC_ERROR_MESSAGE = 'İstek işlenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.';

    public static function isOk(?array $response): bool
    {
        return (bool) ($response['success'] ?? false);
    }

    public static function get(string $endpoint, array $query = []): array
    {
        return self::send('get', $endpoint, $query);
    }

    public static function post(string $endpoint, array $data = []): array
    {
        return self::send('post', $endpoint, $data);
    }

    public static function put(string $endpoint, array $data = []): array
    {
        return self::send('put', $endpoint, $data);
    }

    public static function delete(string $endpoint): array
    {
        return self::send('delete', $endpoint);
    }

    private static function send(string $method, string $endpoint, array $payload = []): array
    {
        $url = self::getBaseUrl().'/'.self::formatEndpoint($endpoint);

        try {
            $request = Http::withHeaders(self::getHeaders())
                ->timeout(self::TIMEOUT_SECONDS)
                ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
                ->acceptJson();

            $response = match ($method) {
                'get' => $request->get($url, $payload),
                'post' => $request->post($url, $payload),
                'put' => $request->put($url, $payload),
                'delete' => $request->delete($url),
                default => throw new \InvalidArgumentException('Unsupported HTTP method.'),
            };

            return $response->successful()
                ? self::successPayload($response)
                : self::failurePayload($response);
        } catch (Throwable $e) {
            Log::error('Backend API isteği başarısız oldu.', [
                'method' => strtoupper($method),
                'endpoint' => $url,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 503,
                'message' => self::GENERIC_ERROR_MESSAGE,
                'errors' => [],
            ];
        }
    }

    private static function successPayload(Response $response): array
    {
        $payload = $response->json();

        if (! is_array($payload)) {
            $payload = ['data' => $payload];
        }

        return array_merge($payload, [
            'success' => true,
            'status' => $response->status(),
        ]);
    }

    private static function failurePayload(Response $response): array
    {
        return [
            'success' => false,
            'status' => $response->status(),
            'message' => $response->json('message') ?? self::GENERIC_ERROR_MESSAGE,
            'errors' => $response->json('errors') ?? [],
        ];
    }

    private static function getBaseUrl(): string
    {
        return rtrim(config('services.backend.url', 'http://backend:8000'), '/').'/api';
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
}
