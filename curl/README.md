# UIID API v1 cURL Cookbook & Workflow Guide

This guide provides standalone, production-ready `cURL` commands for all **UIID API v1** features.

Base API URL: `https://uiid.linkspreed.com`
Official API Documentation: [https://uiid.linkspreed.com/api-docs](https://uiid.linkspreed.com/api-docs)

---

## 01. Authentication (OIDC)

### 1. Initiate Handshake (Authorize URL)
Send users to this URL in a browser:
```bash
https://uiid.linkspreed.com/oauth/authorize?client_id=YOUR_CLIENT_ID&redirect_uri=https://your-app.com/callback&response_type=code&scope=openid%20profile%20email%20alias:read:public
```

### 2. Token Exchange
Exchange the authorization code for an `access_token` and `refresh_token`:
```bash
curl -X POST "https://uiid.linkspreed.com/oauth/token" \
  -d "grant_type=authorization_code" \
  -d "code=CODE_FROM_URL" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "redirect_uri=https://your-app.com/callback"
```

### 3. Refresh Access Token
```bash
curl -X POST "https://uiid.linkspreed.com/oauth/token" \
  -d "grant_type=refresh_token" \
  -d "refresh_token=YOUR_REFRESH_TOKEN" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET"
```

### 4. Fetch User Information
```bash
curl -X GET "https://uiid.linkspreed.com/oauth/userinfo" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

---

## 02. App Management

### Onboard New Application
```bash
curl -X POST "https://uiid.linkspreed.com/api/v1/applications" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "My New App", "redirect_uri": "https://myapp.com/callback"}'
```

### Revoke Application Access
```bash
curl -X DELETE "https://uiid.linkspreed.com/api/v1/applications/app_123" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

---

## 03. Core ID API

### Generate Public UIID String
```bash
curl -X GET "https://uiid.linkspreed.com/api/v1/core/uiid/generate"
```

### Check Identity KYC Status & Trust Score
```bash
curl -X GET "https://uiid.linkspreed.com/api/v1/core/kyc/status" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Store Encrypted Application Data on Core ID
```bash
curl -X POST "https://uiid.linkspreed.com/api/v1/core/data" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"key": "app_config", "value": "settings_encrypted", "is_public": false, "is_immutable": true}'
```

---

## 04. Alias Network & Shared Buckets

### Create Alias
```bash
curl -X POST "https://uiid.linkspreed.com/api/v1/aliases/create" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "Work Identity"}'
```

### Partial Update (Dot-Notation) & Lock Immutable Node
```bash
curl -X PATCH "https://uiid.linkspreed.com/api/v1/aliases/data" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"alias_id": "UIID-Alias-80BB", "profile.theme": "dark", "is_immutable": true}'
```

### Request Immutable Node Deletion
```bash
curl -X POST "https://uiid.linkspreed.com/api/v1/aliases/storage/request-deletion" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"alias_id": "UIID-Alias-80BB", "key": "membership_id"}'
```

### Add Collaborative Member to Shared Bucket (v2.6)
```bash
curl -X POST "https://uiid.linkspreed.com/api/v1/aliases/members" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"alias_id_str": "UIID-Alias-80BB", "target_uiid": "did:uiid:80BB-59CE-1B90", "role": "chat_partner"}'
```

---

## 05. Webhooks & Subscriptions

### Subscribe to Identity Webhook Events
```bash
curl -X POST "https://uiid.linkspreed.com/api/v1/webhooks" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"url": "https://your-api.com/webhook", "events": ["alias.data.created", "alias.data.updated"]}'
```
