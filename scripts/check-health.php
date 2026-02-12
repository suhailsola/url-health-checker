<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use UrlHealthChecker\Repositories\UrlRepository;
use UrlHealthChecker\Services\HealthChecker;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Starting health check for all URLs...\n\n";

try {
    $repository = new UrlRepository();
    $healthChecker = new HealthChecker();
    
    $urls = $repository->findAll();
    
    if (empty($urls)) {
        echo "No URLs to check.\n";
        exit(0);
    }
    
    $stats = ['online' => 0, 'offline' => 0, 'total' => count($urls)];
    
    foreach ($urls as $url) {
        echo "Checking: {$url->url} ";
        
        $result = $healthChecker->check($url->url);
        
        // Update the URL with the check result
        $repository->update($url->id, [
            'status' => $result['status'],
            'last_status_code' => $result['status_code'],
            'last_checked_at' => $result['checked_at'],
        ]);
        
        $statusEmoji = $result['status'] === 'online' ? '✓' : '✗';
        $statusCode = $result['status_code'] ?? 'N/A';
        
        echo "{$statusEmoji} [{$result['status']}] (HTTP {$statusCode})\n";
        
        $stats[$result['status']]++;
    }
    
    echo "\n" . str_repeat('-', 50) . "\n";
    echo "Summary:\n";
    echo "  Total URLs: {$stats['total']}\n";
    echo "  Online: {$stats['online']}\n";
    echo "  Offline: {$stats['offline']}\n";
    echo str_repeat('-', 50) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
