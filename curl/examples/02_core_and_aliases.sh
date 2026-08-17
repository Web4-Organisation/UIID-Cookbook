#!/usr/bin/env bash
set -e

echo "=== 02. Core ID & Alias Network Workflow (cURL) ==="

BASE_URL="https://uiid.linkspreed.com"

echo "1. Generate Core UIID:"
curl -s -X GET "${BASE_URL}/api/v1/core/uiid/generate"
echo ""

echo ""
echo "2. Patch Alias Node Data & Lock Immutable Pair:"
echo "curl -X PATCH \"${BASE_URL}/api/v1/aliases/data\" \\"
echo "  -H \"Authorization: Bearer ACCESS_TOKEN\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"alias_id\": \"UIID-Alias-80BB\", \"profile.theme\": \"dark\", \"is_immutable\": true}'"

echo ""
echo "3. Add Shared Bucket Member (v2.6):"
echo "curl -X POST \"${BASE_URL}/api/v1/aliases/members\" \\"
echo "  -H \"Authorization: Bearer ACCESS_TOKEN\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"alias_id_str\": \"UIID-Alias-80BB\", \"target_uiid\": \"did:uiid:80BB-59CE\", \"role\": \"chat_partner\"}'"
