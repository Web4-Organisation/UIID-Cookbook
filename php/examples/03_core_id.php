<?php

require_once __DIR__ . '/../vendor/autoload.php';

use UIID\SDK\UIIDClient;

echo "=== 03. Core ID API Workflow (PHP) ===\n\n";

echo "[Core ID] Available PHP SDK Methods:\n";
echo " - \$client->generateCoreID()\n";
echo " - \$client->getKycStatus()\n";
echo " - \$client->storeCoreData('app_config', 'encrypted_data', isPublic: false, isImmutable: true)\n";
echo " - \$client->getAuthorizedApplications()\n\n";
