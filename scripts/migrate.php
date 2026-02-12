<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use UrlHealthChecker\Database\Connection;
use UrlHealthChecker\Database\Migrations\CreateUrlsTable;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $pdo = Connection::getInstance();
    
    // Create migrations table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id SERIAL PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Define migrations
    $migrations = [
        'CreateUrlsTable' => new CreateUrlsTable(),
    ];

    // Run migrations
    foreach ($migrations as $name => $migration) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
        $stmt->execute([$name]);
        
        if ($stmt->fetchColumn() == 0) {
            echo "Running migration: {$name}...\n";
            $migration->up($pdo);
            
            $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
            $stmt->execute([$name]);
            
            echo "✓ Migration {$name} completed successfully\n";
        } else {
            echo "- Migration {$name} already executed\n";
        }
    }

    echo "\nAll migrations completed!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
