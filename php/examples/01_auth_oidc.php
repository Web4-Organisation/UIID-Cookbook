<?php

require_once __DIR__ . '/../vendor/autoload.php';

use UIID\SDK\UIIDClient;

echo "=== 01. OIDC & Authentication Workflow (PHP) ===\n\n";

$client = new UIIDClient(clientId: 'app_550e8400', clientSecret: 'sec_f47ac10b');

// 1. Authorize URL
$url = $client->getAuthorizeUrl('https://myapp.com/callback');
echo "[OIDC] 1. Authorize URL:\n  {$url}\n\n";

echo "[OIDC] 2. Available OIDC Methods:\n";
echo " - \$client->exchangeCodeForToken(\$code, \$redirectUri)\n";
echo " - \$client->refreshToken(\$refreshToken)\n";
echo " - \$client->getUserInfo()\n";
echo " - \$client->getOidcDiscovery()\n";
echo " - \$client->getJwks()\n\n";
