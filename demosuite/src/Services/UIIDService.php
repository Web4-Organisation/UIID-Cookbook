<?php
declare(strict_types=1);

namespace UIID\Demosuite\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class UIIDService
{
    private Client $httpClient;
    private string $apiBaseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct(?Client $httpClient = null)
    {
        $this->apiBaseUrl = $_ENV['UIID_API_BASE_URL'];
        $this->clientId = $_ENV['UIID_CLIENT_ID'];
        $this->clientSecret = $_ENV['UIID_CLIENT_SECRET'];
        $this->redirectUri = $_ENV['UIID_REDIRECT_URI'];
        $this->httpClient = $httpClient ?? new Client(['base_uri' => $this->apiBaseUrl]);
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['access_token']);
    }

    public function getAuthorizationUrl(): string
    {
        $queryParams = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'core:read:minimal core:read:sensitive core:write alias:create alias:read:public alias:read:private alias:write audit:read',
        ]);

        return $this->apiBaseUrl . '/oauth/authorize?' . $queryParams;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function exchangeCodeForTokens(string $code, string $redirectUri): array
    {
        try {
            $response = $this->httpClient->post('/oauth/token', [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            return json_decode((string) $response->getBody(), true);
        } catch (ClientException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            $statusCode = $e->getResponse()->getStatusCode();
            throw new \Exception("API Error (Status: {$statusCode}): " . $responseBody);
        }
    }

    public function refreshAccessToken(): array
    {
        try {
            $response = $this->httpClient->post('/oauth/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $_SESSION['refresh_token'],
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            $tokens = json_decode((string) $response->getBody(), true);
            $_SESSION['access_token'] = $tokens['access_token'];
            $_SESSION['token_expires'] = time() + $tokens['expires_in'];
            return $tokens;
        } catch (ClientException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            throw new \Exception('API Error during token refresh: ' . $responseBody);
        }
    }
    
    public function callApi(string $method, string $endpoint, array $params = []): array
    {
        if (!$this->isLoggedIn()) {
            throw new \Exception('Not logged in');
        }

        // Check if token is expired and refresh if needed
        if (isset($_SESSION['token_expires']) && $_SESSION['token_expires'] < time()) {
            $this->refreshAccessToken();
        }

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $_SESSION['access_token'],
                'Accept' => 'application/json',
            ],
        ];

        if ($method === 'GET' && !empty($params)) {
            $options['query'] = $params;
        } elseif ($method === 'POST') {
            $options['json'] = $params;
        }

        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            $body = (string) $response->getBody();
            if (empty($body)) {
                throw new \Exception('API response is empty.');
            }
            $result = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Error decoding API response: ' . json_last_error_msg() . '. Response body: ' . $body);
            }
            return $result;
        } catch (ClientException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            throw new \Exception('API Error: ' . $responseBody);
        }
    }
}
