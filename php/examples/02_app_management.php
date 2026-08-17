<?php

require_once __DIR__ . '/../vendor/autoload.php';

use UIID\SDK\UIIDClient;

echo "=== 02. Application Management Workflow (PHP) ===\n\n";

echo "[App Mgmt] Available PHP SDK Methods:\n";
echo " - \$client->onboardApplication('My New App', 'https://myapp.com/callback')\n";
echo " - \$client->revokeApplication('app_123')\n\n";
