<?php

namespace UIID\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class UIIDClient
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private ?string $accessToken = null;
    private ClientInterface $client;

    public function __construct(
        string $clientId = '',
        string $clientSecret = '',
        string $baseUrl = 'https://uiid.linkspreed.com',
        ?ClientInterface $client = null
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->client = $client ?? new Client(['base_uri' => $this->baseUrl]);
    }

    public function setAccessToken(string $token): void
    {
        $this->accessToken = $token;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    private function request(string $method, string $endpoint, array $options = []): array
    {
        $headers = $options['headers'] ?? [];
        if ($this->accessToken && !isset($headers['Authorization'])) {
            $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        $options['headers'] = $headers;
        $options['http_errors'] = false;

        $response = $this->client->request($method, $endpoint, $options);
        $body = (string) $response->getBody();
        $data = json_decode($body, true) ?? ['raw' => $body];

        return [
            'status_code' => $response->getStatusCode(),
            'ok' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
            'data' => $data,
        ];
    }

    // --- 02. Authentication (OIDC) ---

    public function getAuthorizeUrl(string $redirectUri, string $scope = 'openid profile email alias:read:public'): string
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope,
        ]);

        return $this->baseUrl . '/oauth/authorize?' . $query;
    }

    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $res = $this->request('POST', '/oauth/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $redirectUri,
            ],
        ]);

        if (isset($res['data']['access_token'])) {
            $this->accessToken = $res['data']['access_token'];
        }

        return $res['data'];
    }

    public function refreshToken(string $refreshToken): array
    {
        $res = $this->request('POST', '/oauth/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);

        if (isset($res['data']['access_token'])) {
            $this->accessToken = $res['data']['access_token'];
        }

        return $res['data'];
    }

    public function getUserInfo(): array
    {
        return $this->request('GET', '/oauth/userinfo')['data'];
    }

    public function getOidcDiscovery(): array
    {
        return $this->request('GET', '/.well-known/openid-configuration')['data'];
    }

    public function getJwks(): array
    {
        return $this->request('GET', '/.well-known/jwks.json')['data'];
    }

    // --- 03. App Management ---

    public function onboardApplication(string $name, string $redirectUri): array
    {
        return $this->request('POST', '/api/v1/applications', [
            'json' => ['name' => $name, 'redirect_uri' => $redirectUri],
        ])['data'];
    }

    public function revokeApplication(string $appId): array
    {
        return $this->request('DELETE', "/api/v1/applications/{$appId}")['data'];
    }

    // --- 04. Core ID API ---

    public function generateCoreID(): array
    {
        return $this->request('GET', '/api/v1/core/uiid/generate')['data'];
    }

    public function getKycStatus(): array
    {
        return $this->request('GET', '/api/v1/core/kyc/status')['data'];
    }

    public function storeCoreData(string $key, mixed $value, bool $isPublic = false, bool $isImmutable = false): array
    {
        return $this->request('POST', '/api/v1/core/data', [
            'json' => [
                'key' => $key,
                'value' => $value,
                'is_public' => $isPublic,
                'is_immutable' => $isImmutable,
            ],
        ])['data'];
    }

    public function getAuthorizedApplications(): array
    {
        return $this->request('GET', '/api/v1/core/applications')['data'];
    }

    // --- 05. Alias Network ---

    public function getAliases(): array
    {
        return $this->request('GET', '/api/v1/aliases')['data'];
    }

    public function createAlias(string $name): array
    {
        return $this->request('POST', '/api/v1/aliases/create', [
            'json' => ['name' => $name],
        ])['data'];
    }

    public function updateAliasStatus(string $aliasId, string $status, ?string $secretKey = null): array
    {
        $body = ['status' => $status];
        if ($secretKey) {
            $body['key'] = $secretKey;
        }

        return $this->request('PUT', "/api/v1/aliases/{$aliasId}/status", [
            'json' => $body,
        ])['data'];
    }

    public function getAliasData(string $aliasId): array
    {
        return $this->request('GET', "/api/v1/aliases/data/{$aliasId}")['data'];
    }

    public function overwriteAliasData(string $aliasId, array $data): array
    {
        return $this->request('POST', '/api/v1/aliases/data', [
            'json' => ['alias_id' => $aliasId, 'data' => $data],
        ])['data'];
    }

    public function patchAliasData(array $payload): array
    {
        return $this->request('PATCH', '/api/v1/aliases/data', [
            'json' => $payload,
        ])['data'];
    }

    public function requestNodeDeletion(string $aliasId, string $key): array
    {
        return $this->request('POST', '/api/v1/aliases/storage/request-deletion', [
            'json' => ['alias_id' => $aliasId, 'key' => $key],
        ])['data'];
    }

    public function removeAliasKey(string $aliasId, string $key): array
    {
        return $this->request('DELETE', '/api/v1/aliases/data', [
            'json' => ['alias_id' => $aliasId, 'key' => $key],
        ])['data'];
    }

    public function purgeAlias(string $aliasId): array
    {
        return $this->request('DELETE', "/api/v1/aliases/{$aliasId}")['data'];
    }

    // --- Shared Buckets (v2.6) ---

    public function addAliasMember(string $aliasIdStr, string $targetUiid, string $role): array
    {
        return $this->request('POST', '/api/v1/aliases/members', [
            'json' => [
                'alias_id_str' => $aliasIdStr,
                'target_uiid' => $targetUiid,
                'role' => $role,
            ],
        ])['data'];
    }

    public function getAliasMembers(string $aliasId): array
    {
        return $this->request('GET', "/api/v1/aliases/members/{$aliasId}")['data'];
    }

    // --- 06. Ecosystem & Trust ---

    public function getMarketplaceApps(): array
    {
        return $this->request('GET', '/api/v1/marketplace/apps')['data'];
    }

    public function getMarketplaceAppDetails($appId): array
    {
        return $this->request('GET', "/api/v1/marketplace/apps/{$appId}")['data'];
    }

    public function getBadges(): array
    {
        return $this->request('GET', '/api/v1/badges')['data'];
    }

    public function getUserBadges(): array
    {
        return $this->request('GET', '/api/v1/user/badges')['data'];
    }

    // --- 07. Auditing ---

    public function getCoreAuditLogs(): array
    {
        return $this->request('GET', '/api/v1/audit/core')['data'];
    }

    public function getAliasAuditLogs(string $aliasId): array
    {
        return $this->request('GET', "/api/v1/audit/alias?alias_id={$aliasId}")['data'];
    }

    // --- 08. Local Integration ---

    public function checkAuthStatus(): array
    {
        return $this->request('GET', '/api/v1/auth/check')['data'];
    }

    // --- 10. Webhooks ---

    public function subscribeWebhook(string $url, array $events, ?string $aliasId = null): array
    {
        $body = ['url' => $url, 'events' => $events];
        if ($aliasId) {
            $body['alias_id'] = $aliasId;
        }

        return $this->request('POST', '/api/v1/webhooks', [
            'json' => $body,
        ])['data'];
    }

    public static function verifyWebhookSignature(array|string $payload, string $signature, string $secret): bool
    {
        $bodyStr = is_array($payload) ? json_encode($payload, JSON_UNESCAPED_SLASHES) : $payload;
        $computed = hash_hmac('sha256', $bodyStr, $secret);
        return hash_equals($signature, $computed);
    }
}
