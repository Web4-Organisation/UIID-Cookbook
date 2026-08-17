import json
import hashlib
import hmac
from uiid_client import UIIDClient

def main():
    print("=== 05. Ecosystem, Auditing & Webhooks Workflow (Python) ===\n")

    client = UIIDClient()

    # Webhook signature verification demo
    payload = {"event": "alias.data.updated", "alias_id": "UIID-Alias-80BB"}
    secret = "my_webhook_secret_123"
    body_bytes = json.dumps(payload, separators=(',', ':')).encode('utf-8')
    sig = hmac.new(secret.encode('utf-8'), body_bytes, hashlib.sha256).hexdigest()

    is_valid = UIIDClient.verify_webhook_signature(payload, sig, secret)
    print(f"[Webhooks] HMAC-SHA256 Signature Verification: {is_valid}\n")

    print("[Ecosystem & Audit] Methods available in Python SDK:")
    print(" - client.get_marketplace_apps()")
    print(" - client.get_user_badges()")
    print(" - client.get_core_audit_logs()")
    print(" - client.get_alias_audit_logs(alias_id='UIID-Alias-123')\n")

if __name__ == "__main__":
    main()
