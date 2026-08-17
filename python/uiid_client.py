import hmac
import hashlib
import json
from urllib.parse import urlencode, quote
import requests

class UIIDClient:
    def __init__(self, client_id="", client_secret="", base_url="https://uiid.linkspreed.com", session=None):
        self.client_id = client_id
        self.client_secret = client_secret
        self.base_url = base_url.rstrip("/")
        self.access_token = None
        self.session = session or requests.Session()

    def set_access_token(self, token: str):
        self.access_token = token

    def _request(self, method: str, endpoint: str, params=None, data=None, json_data=None, headers=None):
        url = endpoint if endpoint.startswith("http") else f"{self.base_url}{endpoint}"
        req_headers = headers or {}

        if self.access_token and "Authorization" not in req_headers:
            req_headers["Authorization"] = f"Bearer {self.access_token}"

        response = self.session.request(
            method=method,
            url=url,
            params=params,
            data=data,
            json=json_data,
            headers=req_headers
        )
        try:
            res_data = response.json()
        except Exception:
            res_data = {"text": response.text}

        return {
            "status_code": response.status_code,
            "ok": response.ok,
            "data": res_data
        }

    # --- 02. Authentication (OIDC) ---

    def get_authorize_url(self, redirect_uri: str, scope: str = "openid profile email alias:read:public") -> str:
        params = {
            "client_id": self.client_id,
            "redirect_uri": redirect_uri,
            "response_type": "code",
            "scope": scope
        }
        return f"{self.base_url}/oauth/authorize?{urlencode(params)}"

    def exchange_code_for_token(self, code: str, redirect_uri: str) -> dict:
        payload = {
            "grant_type": "authorization_code",
            "code": code,
            "client_id": self.client_id,
            "client_secret": self.client_secret,
            "redirect_uri": redirect_uri
        }
        res = self._request("POST", "/oauth/token", data=payload)
        if res.get("data") and "access_token" in res["data"]:
            self.access_token = res["data"]["access_token"]
        return res["data"]

    def refresh_token(self, refresh_token: str) -> dict:
        payload = {
            "grant_type": "refresh_token",
            "refresh_token": refresh_token,
            "client_id": self.client_id,
            "client_secret": self.client_secret
        }
        res = self._request("POST", "/oauth/token", data=payload)
        if res.get("data") and "access_token" in res["data"]:
            self.access_token = res["data"]["access_token"]
        return res["data"]

    def get_user_info(self) -> dict:
        res = self._request("GET", "/oauth/userinfo")
        return res["data"]

    def get_oidc_discovery(self) -> dict:
        res = self._request("GET", "/.well-known/openid-configuration")
        return res["data"]

    def get_jwks(self) -> dict:
        res = self._request("GET", "/.well-known/jwks.json")
        return res["data"]

    # --- 03. App Management ---

    def onboard_application(self, name: str, redirect_uri: str) -> dict:
        res = self._request("POST", "/api/v1/applications", json_data={"name": name, "redirect_uri": redirect_uri})
        return res["data"]

    def revoke_application(self, app_id: str) -> dict:
        res = self._request("DELETE", f"/api/v1/applications/{app_id}")
        return res["data"]

    # --- 04. Core ID API ---

    def generate_core_id(self) -> dict:
        res = self._request("GET", "/api/v1/core/uiid/generate")
        return res["data"]

    def get_kyc_status(self) -> dict:
        res = self._request("GET", "/api/v1/core/kyc/status")
        return res["data"]

    def store_core_data(self, key: str, value, is_public: bool = False, is_immutable: bool = False) -> dict:
        payload = {
            "key": key,
            "value": value,
            "is_public": is_public,
            "is_immutable": is_immutable
        }
        res = self._request("POST", "/api/v1/core/data", json_data=payload)
        return res["data"]

    def get_authorized_applications(self) -> dict:
        res = self._request("GET", "/api/v1/core/applications")
        return res["data"]

    # --- 05. Alias Network ---

    def get_aliases(self) -> dict:
        res = self._request("GET", "/api/v1/aliases")
        return res["data"]

    def create_alias(self, name: str) -> dict:
        res = self._request("POST", "/api/v1/aliases/create", json_data={"name": name})
        return res["data"]

    def update_alias_status(self, alias_id: str, status: str, secret_key: str = None) -> dict:
        payload = {"status": status}
        if secret_key:
            payload["key"] = secret_key
        res = self._request("PUT", f"/api/v1/aliases/{alias_id}/status", json_data=payload)
        return res["data"]

    def get_alias_data(self, alias_id: str) -> dict:
        res = self._request("GET", f"/api/v1/aliases/data/{alias_id}")
        return res["data"]

    def overwrite_alias_data(self, alias_id: str, data: dict) -> dict:
        res = self._request("POST", "/api/v1/aliases/data", json_data={"alias_id": alias_id, "data": data})
        return res["data"]

    def patch_alias_data(self, payload: dict) -> dict:
        res = self._request("PATCH", "/api/v1/aliases/data", json_data=payload)
        return res["data"]

    def request_node_deletion(self, alias_id: str, key: str) -> dict:
        res = self._request("POST", "/api/v1/aliases/storage/request-deletion", json_data={"alias_id": alias_id, "key": key})
        return res["data"]

    def remove_alias_key(self, alias_id: str, key: str) -> dict:
        res = self._request("DELETE", "/api/v1/aliases/data", json_data={"alias_id": alias_id, "key": key})
        return res["data"]

    def purge_alias(self, alias_id: str) -> dict:
        res = self._request("DELETE", f"/api/v1/aliases/{alias_id}")
        return res["data"]

    # --- Shared Buckets (v2.6) ---

    def add_alias_member(self, alias_id_str: str, target_uiid: str, role: str) -> dict:
        payload = {"alias_id_str": alias_id_str, "target_uiid": target_uiid, "role": role}
        res = self._request("POST", "/api/v1/aliases/members", json_data=payload)
        return res["data"]

    def get_alias_members(self, alias_id: str) -> dict:
        res = self._request("GET", f"/api/v1/aliases/members/{alias_id}")
        return res["data"]

    # --- 06. Ecosystem & Trust ---

    def get_marketplace_apps(self) -> dict:
        res = self._request("GET", "/api/v1/marketplace/apps")
        return res["data"]

    def get_marketplace_app_details(self, app_id) -> dict:
        res = self._request("GET", f"/api/v1/marketplace/apps/{app_id}")
        return res["data"]

    def get_badges(self) -> dict:
        res = self._request("GET", "/api/v1/badges")
        return res["data"]

    def get_user_badges(self) -> dict:
        res = self._request("GET", "/api/v1/user/badges")
        return res["data"]

    # --- 07. Auditing ---

    def get_core_audit_logs(self) -> dict:
        res = self._request("GET", "/api/v1/audit/core")
        return res["data"]

    def get_alias_audit_logs(self, alias_id: str) -> dict:
        res = self._request("GET", f"/api/v1/audit/alias?alias_id={alias_id}")
        return res["data"]

    # --- 08. Local Integration & Smart Tags ---

    def check_auth_status(self) -> dict:
        res = self._request("GET", "/api/v1/auth/check")
        return res["data"]

    # --- 10. Webhooks ---

    def subscribe_webhook(self, url: str, events: list, alias_id: str = None) -> dict:
        payload = {"url": url, "events": events}
        if alias_id:
            payload["alias_id"] = alias_id
        res = self._request("POST", "/api/v1/webhooks", json_data=payload)
        return res["data"]

    @staticmethod
    def verify_webhook_signature(payload, signature: str, secret: str) -> bool:
        body_bytes = json.dumps(payload, separators=(',', ':')).encode('utf-8') if isinstance(payload, dict) else str(payload).encode('utf-8')
        computed = hmac.new(secret.encode('utf-8'), body_bytes, hashlib.sha256).hexdigest()
        return hmac.compare_digest(signature, computed)
