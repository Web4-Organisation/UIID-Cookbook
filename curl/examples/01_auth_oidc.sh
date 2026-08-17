#!/usr/bin/env bash
set -e

echo "=== 01. OIDC Authentication Workflow (cURL) ==="

BASE_URL="https://uiid.linkspreed.com"
CLIENT_ID="YOUR_CLIENT_ID"
CLIENT_SECRET="YOUR_CLIENT_SECRET"
REDIRECT_URI="https://myapp.com/callback"

echo "1. Redirect user to Authorize URL:"
echo "${BASE_URL}/oauth/authorize?client_id=${CLIENT_ID}&redirect_uri=${REDIRECT_URI}&response_type=code&scope=openid%20profile%20email"

echo ""
echo "2. Exchange Code for Token Command:"
echo "curl -X POST \"${BASE_URL}/oauth/token\" \\"
echo "  -d \"grant_type=authorization_code\" \\"
echo "  -d \"code=CODE_FROM_URL\" \\"
echo "  -d \"client_id=${CLIENT_ID}\" \\"
echo "  -d \"client_secret=${CLIENT_SECRET}\" \\"
echo "  -d \"redirect_uri=${REDIRECT_URI}\""

echo ""
echo "3. Fetch User Info Command:"
echo "curl -X GET \"${BASE_URL}/oauth/userinfo\" -H \"Authorization: Bearer ACCESS_TOKEN\""
