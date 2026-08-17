<?php

namespace UIID\SDK\Tests;

use PHPUnit\Framework\TestCase;
use UIID\SDK\UIIDClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

class UIIDClientTest extends TestCase
{
    public function testGetAuthorizeUrl(): void
    {
        $client = new UIIDClient(clientId: 'test_client_id');
        $url = $client->getAuthorizeUrl('https://app.com/cb');

        $this->assertStringContainsString('client_id=test_client_id', $url);
        $this->assertStringContainsString('redirect_uri=https%3A%2F%2Fapp.com%2Fcb', $url);
    }

    public function testExchangeCodeForToken(): void
    {
        $mockGuzzle = $this->createMock(ClientInterface::class);
        $mockResponse = new Response(200, [], json_encode([
            'access_token' => 'access_token_123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]));

        $mockGuzzle->expects($this->once())
            ->method('request')
            ->with('POST', '/oauth/token', $this->anything())
            ->willReturn($mockResponse);

        $client = new UIIDClient('c1', 's1', client: $mockGuzzle);
        $res = $client->exchangeCodeForToken('code123', 'https://app.com/cb');

        $this->assertEquals('access_token_123', $res['access_token']);
        $this->assertEquals('access_token_123', $client->getAccessToken());
    }

    public function testPatchAliasData(): void
    {
        $mockGuzzle = $this->createMock(ClientInterface::class);
        $mockResponse = new Response(200, [], json_encode([
            'status' => 'success',
            'message' => 'Alias data updated successfully.',
        ]));

        $mockGuzzle->expects($this->once())
            ->method('request')
            ->with('PATCH', '/api/v1/aliases/data', $this->anything())
            ->willReturn($mockResponse);

        $client = new UIIDClient(client: $mockGuzzle);
        $client->setAccessToken('mock_token');

        $res = $client->patchAliasData([
            'alias_id' => 'UIID-Alias-001',
            'profile.theme' => 'dark',
            'is_immutable' => true,
        ]);

        $this->assertEquals('success', $res['status']);
    }

    public function testAddAliasMember(): void
    {
        $mockGuzzle = $this->createMock(ClientInterface::class);
        $mockResponse = new Response(200, [], json_encode([
            'status' => 'success',
            'message' => 'Member added.',
        ]));

        $mockGuzzle->expects($this->once())
            ->method('request')
            ->with('POST', '/api/v1/aliases/members', $this->anything())
            ->willReturn($mockResponse);

        $client = new UIIDClient(client: $mockGuzzle);
        $res = $client->addAliasMember('UIID-Alias-001', 'did:uiid:80BB', 'chat_partner');

        $this->assertEquals('success', $res['status']);
    }

    public function testVerifyWebhookSignature(): void
    {
        $payload = ['event' => 'alias.data.updated', 'alias_id' => 'UIID-Alias-99'];
        $secret = 'secret_key';
        $bodyStr = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $validSig = hash_hmac('sha256', $bodyStr, $secret);

        $this->assertTrue(UIIDClient::verifyWebhookSignature($payload, $validSig, $secret));
        $this->assertFalse(UIIDClient::verifyWebhookSignature($payload, 'invalid_sig', $secret));
    }
}
