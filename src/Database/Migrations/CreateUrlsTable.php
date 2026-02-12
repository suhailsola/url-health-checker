<?php

namespace UrlHealthChecker\Database\Migrations;

use PDO;

class CreateUrlsTable
{
    public function up(PDO $pdo): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS urls (
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
            
            CREATE INDEX IF NOT EXISTS idx_urls_status ON urls(status);
            CREATE INDEX IF NOT EXISTS idx_urls_last_checked_at ON urls(last_checked_at);
        ";

        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void
    {
        $sql = "DROP TABLE IF EXISTS urls CASCADE;";
        $pdo->exec($sql);
    }
}
