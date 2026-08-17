<?php

require_once __DIR__ . '/../vendor/autoload.php';

use UIID\SDK\UIIDClient;

echo "=== 04. Alias Network & Shared Buckets Workflow (PHP) ===\n\n";

echo "[Alias Network] Available PHP SDK Methods:\n";
echo " - \$client->getAliases()\n";
echo " - \$client->createAlias('Work Identity')\n";
echo " - \$client->updateAliasStatus('UIID-Alias-80BB', 'paused')\n";
echo " - \$client->patchAliasData(['alias_id' => '...', 'profile.theme' => 'dark', 'is_immutable' => true])\n";
echo " - \$client->requestNodeDeletion('UIID-Alias-80BB', 'membership_id')\n";
echo " - \$client->addAliasMember('UIID-Alias-80BB', 'did:uiid:80BB...', 'chat_partner') [v2.6 Shared Buckets]\n\n";
