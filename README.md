# UIID API Cookbook & Developer Guide

[![UIID Documentation](https://img.shields.io/badge/API_Docs-uiid.linkspreed.com-blue)](https://uiid.linkspreed.com/api-docs)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![UIID Version](https://img.shields.io/badge/UIID_API-v1.0-green)](#)

Welcome to the official **UIID (Universal Integrated Identity) API Cookbook**. This repository contains production-ready code samples, SDK wrappers, testable examples, and implementation guides across multiple programming languages (**JavaScript/Node.js**, **Python**, **PHP**, **Go**, and **cURL**).

The full and official API documentation is available at:
👉 **[https://uiid.linkspreed.com/api-docs](https://uiid.linkspreed.com/api-docs)**

---

## 🚀 Overview

**UIID (Universal Integrated Identity)** provides a zero-trust, passwordless identity layer for the modern web built on sovereign identity principles.

Key architectural anchors:
1. **Core ID**: The central anchor of a user's digital existence (e.g. `did:uiid:9D1B-3239-5D1D-8399`). Unique and permanent.
2. **Aliases**: Contextual sub-identities (e.g. `UIID-Alias-80BB...`). Disposable and isolated per application context.
3. **Immutable Nodes**: Non-editable data anchors stored on identities. Non-modifiable once locked without explicit user deletion requests.
4. **Shared Buckets**: Real-time collaborative access control between different identities in the same alias context.

---

## 📁 Repository Structure

This repository is structured by language, with each language providing a standard client SDK, integration examples, unit/integration tests, and setup instructions:

```text
.
├── javascript/           # JavaScript / Node.js (CommonJS & ES Modules)
│   ├── src/              # UIID JavaScript Client
│   ├── examples/         # Runnable sample scripts (OIDC, Aliases, Webhooks, Smart Tags)
│   ├── tests/            # Jest test suite with mocked API calls
│   └── package.json
├── python/               # Python 3.8+
│   ├── uiid/             # UIID Python Client Module
│   ├── examples/         # Runnable Python scripts
│   ├── tests/            # pytest unit test suite
│   └── requirements.txt
├── php/                  # PHP 8.1+
│   ├── src/              # UIID PHP Service & Client
│   ├── examples/         # Standalone PHP scripts
│   ├── tests/            # PHPUnit test suite
│   └── composer.json
├── go/                   # Go 1.20+
│   ├── uiid/             # UIID Go Client Package
│   ├── examples/         # Executable Go programs
│   ├── uiid_test.go      # Go testing suite
│   └── go.mod
└── curl/                 # cURL / Shell Scripts
    ├── examples/         # Complete cURL workflows for all endpoints
    └── README.md
```

---

## 🛠 Features Covered in the Cookbook

| Module | Features & Endpoints Covered |
| :--- | :--- |
| **01. Authentication (OIDC)** | `/oauth/authorize`, `/oauth/token` (Authorization Code & Refresh Token flows), `/oauth/userinfo`, `/.well-known/openid-configuration`, `/.well-known/jwks.json` |
| **02. App Management** | Onboard app (`POST /api/v1/applications`), Revoke app (`DELETE /api/v1/applications/{id}`) |
| **03. Core ID API** | Generate public UIID (`GET /api/v1/core/uiid/generate`), Trust/KYC Status (`GET /api/v1/core/kyc/status`), Encrypted KV Storage (`POST /api/v1/core/data`), Auth Audit (`GET /api/v1/core/applications`) |
| **04. Alias Network** | Query Aliases (`GET /api/v1/aliases`), Spawn Alias (`POST /api/v1/aliases/create`), Lifecycle Control (`PUT /api/v1/aliases/{id}/status`), Hierarchical Data Retrieval (`GET /api/v1/aliases/data/{id}`), Overwrite/Patch Data (`POST /PATCH /api/v1/aliases/data`), Request Deletion (`POST /api/v1/aliases/storage/request-deletion`), Key Purge & Permanent Purge |
| **05. Shared Buckets (v2.6)** | Add Collaborator (`POST /api/v1/aliases/members`), Member Registry (`GET /api/v1/aliases/members/{alias_id}`) |
| **06. Ecosystem & Trust** | App Marketplace (`GET /api/v1/marketplace/apps`), Badge Registry (`GET /api/v1/badges`), User Verified Credentials (`GET /api/v1/user/badges`) |
| **07. Auditing** | Core Audit Stream (`GET /api/v1/audit/core`), Alias Action Logs (`GET /api/v1/audit/alias`) |
| **08. Local Integration** | Smart Tags & Silent Auth (`GET /api/v1/auth/check`), Temporary ID Conversion flow |
| **09. Webhooks & Security** | Webhook Subscription (`POST /api/v1/webhooks`), HMAC-SHA256 Signature Verification |

---

## 🔑 Quick Start by Language

### 🟡 JavaScript / Node.js
```bash
cd javascript
npm install
npm test            # Runs the Jest test suite
node examples/01_auth_flow.js
```

### 🐍 Python
```bash
cd python
pip install -r requirements.txt
pytest              # Runs the pytest test suite
python examples/01_auth_flow.py
```

### 🐘 PHP
```bash
cd php
composer install
./vendor/bin/phpunit # Runs PHPUnit test suite
php examples/01_auth_flow.php
```

### 🐹 Go
```bash
cd go
go test -v ./...    # Runs Go unit tests
go run examples/01_auth_flow.go
```

### 🐚 cURL / Bash
```bash
cd curl
./examples/01_oidc_workflow.sh
```

---

## 🔒 Security & Best Practices

1. **HMAC Webhook Verification**: Always verify the `X-UIID-Signature` header using HMAC-SHA256 with your endpoint secret to ensure requests originated from UIID.
2. **Short-Lived Access Tokens**: UIID access tokens expire in 3600s. Use the refresh token flow (`grant_type=refresh_token`) to extend sessions seamlessly.
3. **Immutable Data Nodes**: Once `is_immutable: true` is set on a data key, it cannot be modified by standard updates. Releasing or purging immutable data requires user-initiated deletion requests.
4. **Smart Tag CORS**: When implementing silent auth checks (`/api/v1/auth/check`), ensure your frontend includes `credentials: 'include'`.

---

## 🔗 Official Links & Resources

- **Official UIID API Documentation**: [https://uiid.linkspreed.com/api-docs](https://uiid.linkspreed.com/api-docs)
- **Base API URL**: `https://uiid.linkspreed.com`

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).
