# UIID API v1 Cookbook & Developer Guide

[![UIID API Docs](https://img.shields.io/badge/API_Docs-uiid.linkspreed.com-blue)](https://uiid.linkspreed.com/api-docs)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![UIID Version](https://img.shields.io/badge/UIID_API-v1.0-green)](#)
[![Docker Support](https://img.shields.io/badge/Docker-Compose-blue.svg)](#docker--local-testing)

Welcome to the official **UIID (Universal Integrated Identity) API Cookbook**. This repository is a production-grade, multi-language developer guide, SDK suite, runnable sample collection, and testing kit for **UIID API v1**.

The official and complete API documentation is available at:
👉 **[https://uiid.linkspreed.com/api-docs](https://uiid.linkspreed.com/api-docs)**

---

## 🚀 Architectural Concepts

**UIID (Universal Integrated Identity)** provides a zero-trust, passwordless identity layer for the modern web built on sovereign identity principles:

1. **Core ID**: The permanent, cryptographically unique anchor of a user's digital existence (e.g., `did:uiid:9D1B-3239-5D1D-8399`).
2. **Aliases**: Disposable, contextual sub-identities (e.g., `UIID-Alias-80BB...`) isolated per application context to prevent cross-app tracking.
3. **Immutable Nodes**: Non-editable data anchors stored directly on identity structures. Locks key-value pairs (`is_immutable: true`), requiring explicit user deletion requests to modify or remove.
4. **Shared Buckets (v2.6)**: Real-time collaborative access control between different UIID identities within the same alias context (`POST /api/v1/aliases/members`).
5. **Smart Tags**: Silent, cross-site identity detection using HttpOnly cookie sessions (`GET /api/v1/auth/check`) and temporary ID upgrade flows.

---

## 📁 Repository Structure

The cookbook is organized cleanly by programming language, featuring client SDK wrappers, step-by-step example suites, framework middleware guides, and unit tests for every language:

```text
.
├── docker-compose.yml       # Orchestrates local mock API and multi-language test runners
├── mock-server/             # Express-based local mock API server simulating UIID v1
│   ├── server.js
│   ├── package.json
│   └── Dockerfile
├── javascript/              # Node.js (CommonJS / ES Modules)
│   ├── src/index.js         # UIID JavaScript Client SDK
│   ├── examples/            # 01_auth_oidc.js, 02_app_mgmt.js, 03_core_id.js, etc.
│   ├── tests/client.test.js # Jest/Node native test runner
│   └── package.json
├── python/                  # Python 3.8+
│   ├── uiid_client.py       # UIID Python Client Module
│   ├── examples/            # 01_auth_oidc.py, 02_app_mgmt.py, etc.
│   ├── tests/               # pytest test suite
│   └── requirements.txt
├── php/                     # PHP 8.1+
│   ├── src/UIIDClient.php   # UIID PHP PSR-4 Client Service
│   ├── examples/            # Standalone PHP workflow scripts
│   ├── tests/               # PHPUnit test suite
│   └── composer.json
├── go/                      # Go 1.20+
│   ├── uiid/client.go       # UIID Go Client Package
│   ├── examples/            # Executable Go workflow scripts
│   ├── uiid_test.go         # Go testing suite
│   └── go.mod
└── curl/                    # cURL / Shell Workflows
    ├── README.md            # Comprehensive cURL API cheatsheet
    └── examples/            # Executable shell workflow scripts (.sh)
```

---

## 🛠 Complete Endpoint Coverage Matrix

Every single module from the **UIID API v1 Specification** is implemented and testable:

| Module | Endpoint / Feature | Method | Description |
| :--- | :--- | :--- | :--- |
| **01. OIDC / Auth** | `/oauth/authorize` | `GET` | Initiate passwordless handshake & generate OIDC redirect URL |
| | `/oauth/token` | `POST` | Authorization code exchange & refresh token flow |
| | `/oauth/userinfo` | `GET` | Fetch authenticated identity claims (`sub`, `uiid_core_id`, `email`) |
| | `/.well-known/openid-configuration` | `GET` | Automated OpenID discovery configuration |
| | `/.well-known/jwks.json` | `GET` | RSA public keys for ID token signature validation |
| **02. App Management** | `/api/v1/applications` | `POST` | Onboard new third-party application |
| | `/api/v1/applications/{id}` | `DELETE` | Revoke client application credentials |
| **03. Core ID API** | `/api/v1/core/uiid/generate` | `GET` | Public utility generating cryptographically unique UIID strings |
| | `/api/v1/core/kyc/status` | `GET` | Verification levels (`unverified`, `pending`, `verified`) & trust scores |
| | `/api/v1/core/data` | `POST` | Store encrypted application KV settings on Core ID |
| | `/api/v1/core/applications` | `GET` | Audit list of authorized third-party applications |
| **04. Alias Network** | `/api/v1/aliases` | `GET` | Query user alias registry |
| | `/api/v1/aliases/create` | `POST` | Spawn new application sub-identity |
| | `/api/v1/aliases/{id}/status` | `PUT` | Manage lifecycle status (`active`, `paused`, `archived`, `locked`) |
| | `/api/v1/aliases/data/{id}` | `GET` | Retrieve nested hierarchical JSON storage object |
| | `/api/v1/aliases/data` | `POST`/`PATCH` | Overwrite or dot-notation partial patch & lock immutable nodes |
| | `/api/v1/aliases/storage/request-deletion` | `POST` | Trigger provider deletion notification for immutable keys |
| | `/api/v1/aliases/data` | `DELETE` | Remove specific key-value pair from alias store |
| | `/api/v1/aliases/{id}` | `DELETE` | Permanently purge identity and all associated nodes |
| **05. Shared Buckets** | `/api/v1/aliases/members` | `POST` | Grant collaborative access to target UIID (`role: chat_partner`, etc.) |
| | `/api/v1/aliases/members/{id}` | `GET` | Fetch registry of members in a shared bucket |
| **06. Ecosystem & Trust** | `/api/v1/marketplace/apps` | `GET` | Discover ecosystem apps for cross-platform integration |
| | `/api/v1/badges` | `GET` | Ecosystem credential registry |
| | `/api/v1/user/badges` | `GET` | Verified badges currently owned by the user |
| **07. Auditing** | `/api/v1/audit/core` | `GET` | Core ID primary transaction history |
| | `/api/v1/audit/alias` | `GET` | Activity stream for a specific alias context |
| **08. Smart Tags** | `/api/v1/auth/check` | `GET` | Silent HttpOnly cookie auth detection for smart tags |
| **09. Webhooks** | `/api/v1/webhooks` | `POST` | Subscribe to identity events & receive HMAC-SHA256 secret |

---

## ⚡ Quick Start by Language

### 🟡 JavaScript / Node.js
```bash
cd javascript
npm install
npm test                         # Runs Jest unit tests
node examples/01_auth_oidc.js    # Runs OIDC example workflow
```

### 🐍 Python
```bash
cd python
pip install -r requirements.txt
PYTHONPATH=. python3 -m pytest tests # Runs pytest suite
python3 examples/01_auth_oidc.py    # Runs Python example workflow
```

### 🐘 PHP
```bash
cd php
composer install
./vendor/bin/phpunit tests      # Runs PHPUnit test suite
php examples/01_auth_oidc.php   # Runs PHP example workflow
```

### 🐹 Go
```bash
cd go
go test -v .                     # Runs Go unit tests
go run examples/01_auth_oidc.go  # Runs Go example workflow
```

### 🐚 cURL / Shell
```bash
cd curl
./examples/01_auth_oidc.sh
```

---

## 🐳 Docker & Local Testing

You can spin up a local UIID v1 Mock Server and run test suites across all 4 programming languages simultaneously using Docker Compose:

```bash
docker-compose up --build
```

---

## 🔒 Security Best Practices

1. **HMAC Webhook Signature Verification**: Every incoming webhook payload should be verified against its `X-UIID-Signature` header using HMAC-SHA256 with your endpoint secret.
2. **Short-Lived Access Tokens**: UIID bearer tokens expire in 3600 seconds. Use the refresh token flow (`grant_type=refresh_token`) to seamlessly extend sessions.
3. **Immutable Nodes**: Setting `is_immutable: true` locks node keys. Modifying immutable keys requires user-initiated deletion requests (`/api/v1/aliases/storage/request-deletion`).

---

## 🔗 Documentation & Official Resources

- **Official UIID API Documentation**: [https://uiid.linkspreed.com/api-docs](https://uiid.linkspreed.com/api-docs)
- **Base Endpoint**: `https://uiid.linkspreed.com`

---

## 📄 License

This repository is licensed under the [MIT License](LICENSE).
