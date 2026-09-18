<?php

namespace Tests\Feature;

use App\Services\ApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiServiceTest extends TestCase
{
    public function test_successful_response_uses_success_contract(): void
    {
        Http::fake([
            '*/api/posts' => Http::response(['data' => []], 200),
        ]);

        $response = ApiService::get('posts');

        $this->assertTrue($response['success']);
        $this->assertSame(200, $response['status']);
        $this->assertTrue(ApiService::isOk($response));
    }

    public function test_http_error_uses_failure_contract(): void
    {
        Http::fake([
            '*/api/posts' => Http::response(['message' => 'Yetkisiz'], 403),
        ]);

        $response = ApiService::get('posts');

        $this->assertFalse($response['success']);
        $this->assertSame(403, $response['status']);
        $this->assertSame('Yetkisiz', $response['message']);
        $this->assertFalse(ApiService::isOk($response));
    }

    public function test_connection_errors_are_logged_without_leaking_exception_message(): void
    {
        Event::fake([MessageLogged::class]);

        Http::fake(fn () => throw new ConnectionException('secret internals from curl'));

        $response = ApiService::get('posts');

        $this->assertFalse($response['success']);
        $this->assertSame(503, $response['status']);
        $this->assertSame('İstek işlenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.', $response['message']);
        $this->assertStringNotContainsString('secret internals', $response['message']);

        Event::assertDispatched(MessageLogged::class, function (MessageLogged $event) {
            return $event->level === 'error'
                && str_contains($event->message, 'Backend API isteği başarısız oldu.')
                && ($event->context['message'] ?? '') === 'secret internals from curl';
        });
    }
}
