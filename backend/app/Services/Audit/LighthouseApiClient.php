<?php

namespace App\Services\Audit;

use Illuminate\Support\Facades\Http;
use Exception;

class LighthouseApiClient
{
    private string $serviceUrl;

    public function __construct()
    {
        $this->serviceUrl = env('LIGHTHOUSE_SERVICE_URL', env('LIGHTHOUSE_URL', 'http://lighthouse:3000'));
    }

    public function analyze(string $url): array
    {
        try {
            $response = Http::timeout(180)->post($this->serviceUrl, ['url' => $url]);
        } catch (Exception $e) {
            throw new Exception('Не удалось подключиться к Lighthouse сервису: ' . $e->getMessage());
        }

        if (!$response->successful()) {
            $error = $response->json();
            $statusCode = $response->status();
            throw new Exception('Lighthouse ошибка (HTTP ' . $statusCode . '): ' . ($error['error'] ?? 'Неизвестная ошибка'));
        }

        $lighthouseData = $response->json();

        if (!$lighthouseData || isset($lighthouseData['error'])) {
            throw new Exception('Lighthouse ошибка: ' . ($lighthouseData['error'] ?? 'Неизвестная ошибка'));
        }

        return $lighthouseData;
    }
}

