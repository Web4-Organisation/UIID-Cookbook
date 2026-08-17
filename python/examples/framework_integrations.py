# FastAPI / Flask Authentication Middleware & Dependency Examples for UIID

import hmac
import hashlib
import json
from functools import wraps
from uiid_client import UIIDClient

# 1. FastAPI Dependency Example
# Usage:
# @app.get("/secure-endpoint")
# async def secure_route(user: dict = Depends(get_current_uiid_user)):
#     return {"status": "authenticated", "user": user}

"""
async function get_current_uiid_user(authorization: str = Header(...)):
    if not authorization.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Invalid token scheme")

    token = authorization.split(" ")[1]
    client = UIIDClient()
    client.set_access_token(token)

    user_info = client.get_user_info()
    if not user_info or "sub" not in user_info:
        raise HTTPException(status_code=403, detail="Invalid UIID session")
    return user_info
"""

# 2. Flask Webhook HMAC Decorator
def verify_uiid_webhook(webhook_secret: str):
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            from flask import request, abort
            signature = request.headers.get("X-UIID-Signature")
            if not signature:
                abort(401, description="Missing X-UIID-Signature")

            payload = request.get_json(silent=True) or request.get_data(as_text=True)
            if not UIIDClient.verify_webhook_signature(payload, signature, webhook_secret):
                abort(403, description="Invalid HMAC signature")

            return f(*args, **kwargs)
        return decorated_function
    return decorator
