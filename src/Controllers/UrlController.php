<?php

namespace UrlHealthChecker\Controllers;

use Exception;
use UrlHealthChecker\Repositories\UrlRepository;
use UrlHealthChecker\Services\HealthChecker;
use UrlHealthChecker\Router;

class UrlController
{
    private UrlRepository $repository;
    private HealthChecker $healthChecker;
    private Router $router;

    public function __construct(
        ?UrlRepository $repository = null,
        ?HealthChecker $healthChecker = null,
        ?Router $router = null
    ) {
        $this->repository = $repository ?? new UrlRepository();
        $this->healthChecker = $healthChecker ?? new HealthChecker();
        $this->router = $router ?? new Router();
    }

    public function create(): array
    {
        try {
            $data = $this->router->getRequestBody();

            if (empty($data['url'])) {
                return ['error' => 'URL is required', 'status' => 400];
            }

            $url = $this->repository->create(
                $data['url'],
                $data['name'] ?? null
            );

            return [
                'message' => 'URL added successfully',
                'data' => $url->toArray(),
                'status' => 201
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'status' => 400];
        }
    }

    public function list(): array
    {
        try {
            $urls = $this->repository->findAll();

            return [
                'data' => array_map(fn($url) => $url->toArray(), $urls),
                'count' => count($urls),
                'status' => 200
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function get(string $id): array
    {
        try {
            $url = $this->repository->findById((int)$id);

            if (!$url) {
                return ['error' => 'URL not found', 'status' => 404];
            }

            return ['data' => $url->toArray(), 'status' => 200];
        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function update(string $id): array
    {
        try {
            $data = $this->router->getRequestBody();

            if (empty($data)) {
                return ['error' => 'No data provided', 'status' => 400];
            }

            $url = $this->repository->update((int)$id, $data);

            if (!$url) {
                return ['error' => 'URL not found', 'status' => 404];
            }

            return [
                'message' => 'URL updated successfully',
                'data' => $url->toArray(),
                'status' => 200
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'status' => 400];
        }
    }

    public function delete(string $id): array
    {
        try {
            $deleted = $this->repository->delete((int)$id);

            if (!$deleted) {
                return ['error' => 'URL not found', 'status' => 404];
            }

            return ['message' => 'URL deleted successfully', 'status' => 200];
        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function checkHealth(string $id): array
    {
        try {
            $url = $this->repository->findById((int)$id);

            if (!$url) {
                return ['error' => 'URL not found', 'status' => 404];
            }

            $result = $this->healthChecker->check($url->url);

            // Update the URL with the check result
            $updatedUrl = $this->repository->update((int)$id, [
                'status' => $result['status'],
                'last_status_code' => $result['status_code'],
                'last_checked_at' => $result['checked_at'],
            ]);

            return [
                'message' => 'Health check completed',
                'data' => $updatedUrl->toArray(),
                'status' => 200
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }
}
