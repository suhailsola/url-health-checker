<?php

namespace UrlHealthChecker\Tests\Integration;

use PHPUnit\Framework\TestCase;
use PDO;

class UrlApiTest extends TestCase
{
    private PDO $pdo;
    private string $baseUrl = 'http://localhost:8000/api';

    protected function setUp(): void
    {
        // Check if server is running before running integration tests
        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 0) {
            $this->markTestSkipped(
                'Integration tests require a running server. Start with: php -S localhost:8000 -t public'
            );
        }
        
        // Setup test database
        $this->pdo = new PDO(
            sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $_ENV['DB_HOST'],
                $_ENV['DB_PORT'],
                $_ENV['DB_NAME']
            ),
            $_ENV['DB_USER'],
            $_ENV['DB_PASSWORD']
        );
        
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Clean and recreate tables
        $this->pdo->exec("
            DROP TABLE IF EXISTS urls CASCADE;
            CREATE TABLE urls (
                id SERIAL PRIMARY KEY,
                url VARCHAR(2048) NOT NULL UNIQUE,
                name VARCHAR(255),
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                last_checked_at TIMESTAMP,
                last_status_code INTEGER,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT status_check CHECK (status IN ('pending', 'online', 'offline'))
            );
        ");
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS urls CASCADE");
    }

    private function makeRequest(string $method, string $endpoint, ?array $data = null): array
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $statusCode,
            'body' => json_decode($response, true),
        ];
    }

    public function testCreateUrl(): void
    {
        $response = $this->makeRequest('POST', '/urls', [
            'url' => 'https://example.com',
            'name' => 'Example',
        ]);
        
        $this->assertEquals(201, $response['status']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertEquals('https://example.com', $response['body']['data']['url']);
        $this->assertEquals('Example', $response['body']['data']['name']);
    }

    public function testCreateUrlWithoutName(): void
    {
        $response = $this->makeRequest('POST', '/urls', [
            'url' => 'https://google.com',
        ]);
        
        $this->assertEquals(201, $response['status']);
        $this->assertNull($response['body']['data']['name']);
    }

    public function testCreateUrlValidationError(): void
    {
        $response = $this->makeRequest('POST', '/urls', [
            'url' => 'invalid-url',
        ]);
        
        $this->assertEquals(400, $response['status']);
        $this->assertArrayHasKey('error', $response['body']);
    }

    public function testListUrls(): void
    {
        // Create some URLs first
        $this->makeRequest('POST', '/urls', ['url' => 'https://example.com']);
        $this->makeRequest('POST', '/urls', ['url' => 'https://google.com']);
        
        $response = $this->makeRequest('GET', '/urls');
        
        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertCount(2, $response['body']['data']);
        $this->assertEquals(2, $response['body']['count']);
    }

    public function testGetUrl(): void
    {
        $created = $this->makeRequest('POST', '/urls', [
            'url' => 'https://example.com',
        ]);
        
        $id = $created['body']['data']['id'];
        $response = $this->makeRequest('GET', "/urls/{$id}");
        
        $this->assertEquals(200, $response['status']);
        $this->assertEquals('https://example.com', $response['body']['data']['url']);
    }

    public function testGetUrlNotFound(): void
    {
        $response = $this->makeRequest('GET', '/urls/999');
        
        $this->assertEquals(404, $response['status']);
        $this->assertArrayHasKey('error', $response['body']);
    }

    public function testUpdateUrl(): void
    {
        $created = $this->makeRequest('POST', '/urls', [
            'url' => 'https://example.com',
        ]);
        
        $id = $created['body']['data']['id'];
        $response = $this->makeRequest('PUT', "/urls/{$id}", [
            'name' => 'Updated Name',
        ]);
        
        $this->assertEquals(200, $response['status']);
        $this->assertEquals('Updated Name', $response['body']['data']['name']);
    }

    public function testDeleteUrl(): void
    {
        $created = $this->makeRequest('POST', '/urls', [
            'url' => 'https://example.com',
        ]);
        
        $id = $created['body']['data']['id'];
        $response = $this->makeRequest('DELETE', "/urls/{$id}");
        
        $this->assertEquals(200, $response['status']);
        
        // Verify it's deleted
        $getResponse = $this->makeRequest('GET', "/urls/{$id}");
        $this->assertEquals(404, $getResponse['status']);
    }
}
