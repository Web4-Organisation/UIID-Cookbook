package uiid_test

import (
	"crypto/hmac"
	"crypto/sha256"
	"encoding/hex"
	"encoding/json"
	"net/http"
	"net/http/httptest"
	"strings"
	"testing"

	"uiid.linkspreed.com/cookbook/go/uiid"
)

func TestGetAuthorizeURL(t *testing.T) {
	client := uiid.NewClient("client_123", "secret_abc", "")
	url := client.GetAuthorizeURL("https://app.com/cb", "openid profile")

	if !strings.Contains(url, "client_id=client_123") {
		t.Errorf("expected client_id in URL, got %s", url)
	}
	if !strings.Contains(url, "redirect_uri=https%3A%2F%2Fapp.com%2Fcb") {
		t.Errorf("expected redirect_uri in URL, got %s", url)
	}
}

func TestExchangeCodeForToken(t *testing.T) {
	ts := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/oauth/token" {
			t.Errorf("unexpected path: %s", r.URL.Path)
		}
		w.Header().Set("Content-Type", "application/json")
		w.Write([]byte(`{"access_token":"token_go_123","token_type":"Bearer"}`))
	}))
	defer ts.Close()

	client := uiid.NewClient("client_123", "secret_abc", ts.URL)
	res, err := client.ExchangeCodeForToken("code_123", "https://app.com/cb")
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}

	if res["access_token"] != "token_go_123" {
		t.Errorf("expected access_token token_go_123, got %v", res["access_token"])
	}
	if client.AccessToken != "token_go_123" {
		t.Errorf("expected client token_go_123, got %s", client.AccessToken)
	}
}

func TestPatchAliasData(t *testing.T) {
	ts := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.Method != "PATCH" || r.URL.Path != "/api/v1/aliases/data" {
			t.Errorf("unexpected request: %s %s", r.Method, r.URL.Path)
		}
		w.Header().Set("Content-Type", "application/json")
		w.Write([]byte(`{"status":"success","message":"updated"}`))
	}))
	defer ts.Close()

	client := uiid.NewClient("c", "s", ts.URL)
	client.SetAccessToken("mock_token")

	res, err := client.PatchAliasData(map[string]interface{}{"alias_id": "UIID-123", "is_immutable": true})
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}

	if res["status"] != "success" {
		t.Errorf("expected status success, got %v", res["status"])
	}
}

func TestVerifyWebhookSignature(t *testing.T) {
	payload := map[string]string{"event": "alias.data.updated", "alias_id": "UIID-99"}
	secret := "webhook_secret"

	jsonBytes, _ := json.Marshal(payload)
	mac := hmac.New(sha256.New, []byte(secret))
	mac.Write(jsonBytes)
	validSig := hex.EncodeToString(mac.Sum(nil))

	if !uiid.VerifyWebhookSignature(payload, validSig, secret) {
		t.Errorf("expected signature to be valid")
	}

	if uiid.VerifyWebhookSignature(payload, "invalid_sig", secret) {
		t.Errorf("expected signature to be invalid")
	}
}
