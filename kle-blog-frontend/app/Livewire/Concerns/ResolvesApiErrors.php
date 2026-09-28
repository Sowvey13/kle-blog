<?php

namespace App\Livewire\Concerns;

trait ResolvesApiErrors
{
    protected function apiErrorMessage(array $response, string $unavailableMessage): string
    {
        return match ((int) ($response['status'] ?? 0)) {
            401 => 'Oturumunuz sona ermiş olabilir. Lütfen tekrar giriş yapın.',
            403 => 'Bu işlemi gerçekleştirme yetkiniz yok.',
            404 => 'İstenen içerik bulunamadı veya kaldırılmış.',
            422 => $this->firstValidationError($response) ?? 'Girdiğiniz bilgileri kontrol edin.',
            429 => 'Çok fazla istek gönderildi. Lütfen biraz bekleyip tekrar deneyin.',
            default => $unavailableMessage,
        };
    }

    private function firstValidationError(array $response): ?string
    {
        $errors = $response['errors'] ?? [];

        if (! is_array($errors) || $errors === []) {
            return null;
        }

        $first = collect($errors)->flatten()->first();

        return is_string($first) && $first !== '' ? $first : null;
    }
}
