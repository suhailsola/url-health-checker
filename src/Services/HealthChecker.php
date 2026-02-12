<?php

namespace UrlHealthChecker\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HealthChecker
{
    private Client $client;
    private int $timeout;

    public function __construct(?Client $client = null, ?int $timeout = null)
    {
        $this->client = $client ?? new Client();
        $this->timeout = $timeout ?? (int)($_ENV['HEALTH_CHECK_TIMEOUT'] ?? 5);
    }

    public function check(string $url): array
    {
        try {
            $response = $this->client->request('GET', $url, [
                'timeout' => $this->timeout,
                'http_errors' => false, // Don't throw exceptions on 4xx/5xx
            ]);

            $statusCode = $response->getStatusCode();
            $isOnline = $statusCode >= 200 && $statusCode < 400;

            return [
                'status' => $isOnline ? 'online' : 'offline',
                'status_code' => $statusCode,
                'checked_at' => date('Y-m-d H:i:s'),
            ];
        } catch (GuzzleException $e) {
            return [
                'status' => 'offline',
                'status_code' => null,
                'checked_at' => date('Y-m-d H:i:s'),
                'error' => $e->getMessage(),
            ];
        }
    }
}
