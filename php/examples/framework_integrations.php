<?php

namespace UIID\SDK\Integrations;

use UIID\SDK\UIIDClient;

/**
 * Symfony / Laravel UIID Authentication Guard & Middleware Examples
 */
class FrameworkIntegrations
{
    /**
     * PSR-7 / Laravel Webhook Verification Middleware
     */
    public static function verifyWebhookMiddleware(array $headers, string $body, string $secret): bool
    {
        $signature = $headers['x-uiid-signature'][0] ?? $headers['X-UIID-Signature'] ?? null;
        if (!$signature) {
            return false;
        }

        return UIIDClient::verifyWebhookSignature($body, $signature, $secret);
    }
}
