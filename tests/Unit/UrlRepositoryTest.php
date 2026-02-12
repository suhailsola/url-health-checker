<?php

namespace UrlHealthChecker\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UrlHealthChecker\Models\Url;
use UrlHealthChecker\Repositories\UrlRepository;
use PDO;

class UrlRepositoryTest extends TestCase
{
    private PDO $pdo;
    private UrlRepository $repository;

    protected function setUp(): void
    {
        // Use in-memory PostgreSQL-compatible database for testing
        // Note: For actual testing, you'll need a test PostgreSQL database
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
        
        // Create tables
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
        
        $this->repository = new UrlRepository($this->pdo);
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS urls CASCADE");
    }

    public function testCreateUrl(): void
    {
        $url = $this->repository->create('https://example.com', 'Example Site');
        
        $this->assertInstanceOf(Url::class, $url);
        $this->assertEquals('https://example.com', $url->url);
        $this->assertEquals('Example Site', $url->name);
        $this->assertEquals('pending', $url->status);
        $this->assertNotNull($url->id);
    }

    public function testFindAll(): void
    {
        $this->repository->create('https://example.com');
        $this->repository->create('https://google.com');
        
        $urls = $this->repository->findAll();
        
        $this->assertCount(2, $urls);
        $this->assertContainsOnlyInstancesOf(Url::class, $urls);
    }

    public function testFindById(): void
    {
        $created = $this->repository->create('https://example.com');
        $found = $this->repository->findById($created->id);
        
        $this->assertNotNull($found);
        $this->assertEquals($created->id, $found->id);
        $this->assertEquals('https://example.com', $found->url);
    }

    public function testFindByIdNotFound(): void
    {
        $found = $this->repository->findById(999);
        
        $this->assertNull($found);
    }

    public function testUpdate(): void
    {
        $url = $this->repository->create('https://example.com');
        
        $updated = $this->repository->update($url->id, [
            'name' => 'Updated Name',
            'status' => 'online',
            'last_status_code' => 200,
        ]);
        
        $this->assertNotNull($updated);
        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('online', $updated->status);
        $this->assertEquals(200, $updated->lastStatusCode);
    }

    public function testDelete(): void
    {
        $url = $this->repository->create('https://example.com');
        
        $deleted = $this->repository->delete($url->id);
        $this->assertTrue($deleted);
        
        $found = $this->repository->findById($url->id);
        $this->assertNull($found);
    }

    public function testDeleteNotFound(): void
    {
        $deleted = $this->repository->delete(999);
        $this->assertFalse($deleted);
    }
}
