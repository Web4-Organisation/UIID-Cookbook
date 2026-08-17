import json
import hashlib
import hmac
import pytest
from unittest.mock import MagicMock
from uiid_client import UIIDClient

def test_authorize_url():
    client = UIIDClient(client_id="client_xyz")
    url = client.get_authorize_url("https://app.com/callback")
    assert "client_id=client_xyz" in url
    assert "redirect_uri=https%3A%2F%2Fapp.com%2Fcallback" in url

def test_mock_token_exchange():
    mock_session = MagicMock()
    mock_response = MagicMock()
    mock_response.status_code = 200
    mock_response.ok = True
    mock_response.json.return_value = {
        "access_token": "token_12345",
        "token_type": "Bearer",
        "expires_in": 3600
    }
    mock_session.request.return_value = mock_response

    client = UIIDClient(client_id="c1", client_secret="s1", session=mock_session)
    data = client.exchange_code_for_token("code_abc", "https://app.com/callback")

    assert data["access_token"] == "token_12345"
    assert client.access_token == "token_12345"

def test_alias_data_patch():
    mock_session = MagicMock()
    mock_response = MagicMock()
    mock_response.status_code = 200
    mock_response.ok = True
    mock_response.json.return_value = {"status": "success", "message": "Patched"}
    mock_session.request.return_value = mock_response

    client = UIIDClient(session=mock_session)
    client.set_access_token("valid_bearer")

    res = client.patch_alias_data({
        "alias_id": "UIID-Alias-001",
        "profile.theme": "cyber",
        "is_immutable": True
    })

    assert res["status"] == "success"

def test_shared_buckets():
    mock_session = MagicMock()
    mock_response = MagicMock()
    mock_response.status_code = 200
    mock_response.ok = True
    mock_response.json.return_value = {"status": "success", "message": "Member added"}
    mock_session.request.return_value = mock_response

    client = UIIDClient(session=mock_session)
    res = client.add_alias_member("UIID-Alias-001", "did:uiid:80BB", "chat_partner")

    assert res["status"] == "success"

def test_webhook_signature():
    payload = {"event": "alias.data.updated", "alias_id": "UIID-Alias-99"}
    secret = "my_secret_key"
    body_bytes = json.dumps(payload, separators=(',', ':')).encode('utf-8')
    valid_sig = hmac.new(secret.encode('utf-8'), body_bytes, hashlib.sha256).hexdigest()

    assert UIIDClient.verify_webhook_signature(payload, valid_sig, secret) is True
    assert UIIDClient.verify_webhook_signature(payload, "invalid_signature", secret) is False
