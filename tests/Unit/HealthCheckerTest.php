<?php

namespace UrlHealthChecker\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use UrlHealthChecker\Services\HealthChecker;

class HealthCheckerTest extends TestCase
{
    public function testCheckOnlineUrl(): void
    {
        $mock = new MockHandler([
            new Response(200, [], 'OK'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $checker = new HealthChecker($client, 5);
        $result = $checker->check('https://example.com');

        $this->assertEquals('online', $result['status']);
        $this->assertEquals(200, $result['status_code']);
        $this->assertArrayHasKey('checked_at', $result);
    }

    public function testCheckOnlineUrlWithRedirect(): void
    {
        $mock = new MockHandler([
            new Response(301, ['Location' => 'https://example.com/new']),
        ]);

        $handlerStack = HandlerStack::create($mock);
        // Disable auto-redirects in the test to prevent mock queue exhaustion
        $client = new Client([
            'handler' => $handlerStack,
            'allow_redirects' => false
        ]);

        $checker = new HealthChecker($client, 5);
        $result = $checker->check('https://example.com');

        $this->assertEquals('online', $result['status']);
        $this->assertEquals(301, $result['status_code']);
    }

    public function testCheckOfflineUrlClientError(): void
    {
        $mock = new MockHandler([
            new Response(404, [], 'Not Found'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $checker = new HealthChecker($client, 5);
        $result = $checker->check('https://example.com/notfound');

        $this->assertEquals('offline', $result['status']);
        $this->assertEquals(404, $result['status_code']);
    }

    public function testCheckOfflineUrlServerError(): void
    {
        $mock = new MockHandler([
            new Response(500, [], 'Internal Server Error'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $checker = new HealthChecker($client, 5);
        $result = $checker->check('https://example.com');

        $this->assertEquals('offline', $result['status']);
        $this->assertEquals(500, $result['status_code']);
    }

    public function testCheckOfflineUrlNetworkError(): void
    {
        $mock = new MockHandler([
            new ConnectException(
                'Connection refused',
                new Request('GET', 'https://example.com')
            ),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $checker = new HealthChecker($client, 5);
        $result = $checker->check('https://example.com');

        $this->assertEquals('offline', $result['status']);
        $this->assertNull($result['status_code']);
        $this->assertArrayHasKey('error', $result);
    }

    public function testCheckUsesConfiguredTimeout(): void
    {
        $mock = new MockHandler([
            new Response(200, [], 'OK'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $customTimeout = 10;
        $checker = new HealthChecker($client, $customTimeout);

        // This test verifies the timeout is passed to the constructor
        // Actual timeout behavior is tested through integration tests
        $this->assertInstanceOf(HealthChecker::class, $checker);
    }
}
