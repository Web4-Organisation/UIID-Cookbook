import json
from uiid_client import UIIDClient

def main():
    print("=== 01. OIDC & Authentication Workflow (Python) ===\n")

    client = UIIDClient(
        client_id="app_550e8400_client_id",
        client_secret="sec_f47ac10b_client_secret"
    )

    # 1. Authorize URL
    auth_url = client.get_authorize_url("https://myapp.com/callback")
    print("[OIDC] 1. Authorize URL:\n ", auth_url, "\n")

    print("[OIDC] 2. OIDC Discovery & JWKS Specs Available via SDK Methods:")
    print(" - client.get_oidc_discovery()")
    print(" - client.get_jwks()\n")

if __name__ == "__main__":
    main()
