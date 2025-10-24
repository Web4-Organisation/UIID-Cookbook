<?php
declare(strict_types=1);

namespace UIID\Demosuite\Tests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use UIID\Demosuite\Services\UIIDService;

class UIIDServiceTest extends TestCase
{
    protected function setUp(): void
    {
        // Mock environment variables required by the service
        $_ENV['UIID_API_BASE_URL'] = 'http://fake.test';
        $_ENV['UIID_CLIENT_ID'] = 'test_client_id';
        $_ENV['UIID_CLIENT_SECRET'] = 'test_client_secret';
        $_ENV['UIID_REDIRECT_URI'] = 'http://localhost/callback';
        
        // Mock session
        $_SESSION = [];
    }

    public function testGetAuthorizationUrl(): void
    {
        $service = new UIIDService();
        $url = $service->getAuthorizationUrl();

        $this->assertStringStartsWith('http://fake.test/oauth/authorize', $url);
        $this->assertStringContainsString('response_type=code', $url);
        $this->assertStringContainsString('client_id=test_client_id', $url);
    }

    public function testExchangeCodeForTokens(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'access_token' => 'fake_access_token',
                'refresh_token' => 'fake_refresh_token',
                'expires_in' => 3600,
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new UIIDService($client);
        $tokens = $service->exchangeCodeForTokens('test_code');

        $this->assertEquals('fake_access_token', $tokens['access_token']);
        $this->assertEquals('fake_refresh_token', $tokens['refresh_token']);
    }
    
    public function testCallApiSuccess(): void
    {
        $_SESSION['access_token'] = 'fake_access_token';

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'status' => 'success',
                'kyc_status' => 'verified'
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'base_uri' => $_ENV['UIID_API_BASE_URL']]);

        $service = new UIIDService($client);
        $result = $service->callApi('GET', '/api/v1/core/kyc/status');

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('verified', $result['kyc_status']);
    }
}
