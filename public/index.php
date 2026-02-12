<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use UrlHealthChecker\Router;
use UrlHealthChecker\Controllers\UrlController;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Initialize router and controller
$router = new Router();
$controller = new UrlController(router: $router);

// Define routes
$router->addRoute('POST', '/api/urls', [$controller, 'create']);
$router->addRoute('GET', '/api/urls', [$controller, 'list']);
$router->addRoute('GET', '/api/urls/{id}', [$controller, 'get']);
$router->addRoute('PUT', '/api/urls/{id}', [$controller, 'update']);
$router->addRoute('DELETE', '/api/urls/{id}', [$controller, 'delete']);
$router->addRoute('POST', '/api/urls/{id}/check', [$controller, 'checkHealth']);

// Get request URI and method
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Dispatch the request
try {
    if (str_starts_with($uri, '/api/')) {
        $router->dispatch($method, $uri);
    } else {
        // Serve the frontend index.html for all non-API routes
        $indexPath = __DIR__ . '/index.html';
        if (file_exists($indexPath)) {
            readfile($indexPath);
        } else {
            // Fallback or development message
            echo "<h1>URL Health Checker API</h1><p>API is running. Frontend not found.</p>";
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
}
