<?php

namespace UrlHealthChecker\Repositories;

use DateTime;
use PDO;
use UrlHealthChecker\Database\Connection;
use UrlHealthChecker\Models\Url;

class UrlRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connection::getInstance();
    }

    public function create(string $url, ?string $name = null): Url
    {
        $sql = "INSERT INTO urls (url, name, status, created_at, updated_at) 
                VALUES (:url, :name, 'pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) 
                RETURNING *";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'url' => $url,
            'name' => $name,
        ]);

        $row = $stmt->fetch();
        return $this->mapRowToUrl($row);
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM urls ORDER BY created_at DESC";
        $stmt = $this->pdo->query($sql);
        
        $urls = [];
        while ($row = $stmt->fetch()) {
            $urls[] = $this->mapRowToUrl($row);
        }
        
        return $urls;
    }

    public function findById(int $id): ?Url
    {
        $sql = "SELECT * FROM urls WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $row = $stmt->fetch();
        return $row ? $this->mapRowToUrl($row) : null;
    }

    public function update(int $id, array $data): ?Url
    {
        $allowedFields = ['url', 'name', 'status', 'last_checked_at', 'last_status_code'];
        $updates = [];
        $params = ['id' => $id];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
        }

        if (empty($updates)) {
            return $this->findById($id);
        }

        $updates[] = "updated_at = CURRENT_TIMESTAMP";
        $sql = "UPDATE urls SET " . implode(', ', $updates) . " WHERE id = :id RETURNING *";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        return $row ? $this->mapRowToUrl($row) : null;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM urls WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->rowCount() > 0;
    }

    private function mapRowToUrl(array $row): Url
    {
        return new Url(
            id: (int)$row['id'],
            url: $row['url'],
            name: $row['name'],
            status: $row['status'],
            lastCheckedAt: $row['last_checked_at'] ? new DateTime($row['last_checked_at']) : null,
            lastStatusCode: $row['last_status_code'] ? (int)$row['last_status_code'] : null,
            createdAt: new DateTime($row['created_at']),
            updatedAt: new DateTime($row['updated_at'])
        );
    }
}
