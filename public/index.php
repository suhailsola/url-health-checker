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
    $router->dispatch($method, $uri);
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
}
