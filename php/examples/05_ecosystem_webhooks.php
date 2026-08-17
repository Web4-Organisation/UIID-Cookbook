<?php

require_once __DIR__ . '/../vendor/autoload.php';

use UIID\SDK\UIIDClient;

echo "=== 05. Ecosystem, Auditing & Webhooks Workflow (PHP) ===\n\n";

$payload = ['event' => 'alias.data.updated', 'alias_id' => 'UIID-Alias-80BB'];
$secret = 'whsec_secret_123';
$bodyStr = json_encode($payload, JSON_UNESCAPED_SLASHES);
$signature = hash_hmac('sha256', $bodyStr, $secret);

$isValid = UIIDClient::verifyWebhookSignature($payload, $signature, $secret);
echo "[Webhooks] HMAC Verification: " . ($isValid ? 'PASSED' : 'FAILED') . "\n\n";

echo "[Ecosystem & Audit] Available PHP SDK Methods:\n";
echo " - \$client->getMarketplaceApps()\n";
echo " - \$client->getUserBadges()\n";
echo " - \$client->getCoreAuditLogs()\n";
echo " - \$client->getAliasAuditLogs('UIID-Alias-80BB')\n\n";
