package uiid

import (
	"bytes"
	"crypto/hmac"
	"crypto/sha256"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"strings"
	"time"
)

type Client struct {
	BaseURL      string
	ClientID     string
	ClientSecret string
	AccessToken  string
	HTTPClient   *http.Client
}

func NewClient(clientID, clientSecret string, baseURL string) *Client {
	if baseURL == "" {
		baseURL = "https://uiid.linkspreed.com"
	}
	return &Client{
		BaseURL:      strings.TrimRight(baseURL, "/"),
		ClientID:     clientID,
		ClientSecret: clientSecret,
		HTTPClient:   &http.Client{Timeout: 10 * time.Second},
	}
}

func (c *Client) SetAccessToken(token string) {
	c.AccessToken = token
}

func (c *Client) doRequest(method, endpoint string, body interface{}, result interface{}) error {
	reqURL := endpoint
	if !strings.HasPrefix(endpoint, "http") {
		reqURL = c.BaseURL + endpoint
	}

	var reqBody io.Reader
	if body != nil {
		jsonBytes, err := json.Marshal(body)
		if err != nil {
			return err
		}
		reqBody = bytes.NewBuffer(jsonBytes)
	}

	req, err := http.NewRequest(method, reqURL, reqBody)
	if err != nil {
		return err
	}

	req.Header.Set("Content-Type", "application/json")
	if c.AccessToken != "" {
		req.Header.Set("Authorization", "Bearer "+c.AccessToken)
	}

	resp, err := c.HTTPClient.Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	respBytes, err := io.ReadAll(resp.Body)
	if err != nil {
		return err
	}

	if result != nil {
		if err := json.Unmarshal(respBytes, result); err != nil {
			return fmt.Errorf("failed to decode response: %w, raw: %s", err, string(respBytes))
		}
	}

	return nil
}

// OIDC Authorize URL
func (c *Client) GetAuthorizeURL(redirectURI, scope string) string {
	if scope == "" {
		scope = "openid profile email alias:read:public"
	}
	v := url.Values{}
	v.Set("client_id", c.ClientID)
	v.Set("redirect_uri", redirectURI)
	v.Set("response_type", "code")
	v.Set("scope", scope)
	return c.BaseURL + "/oauth/authorize?" + v.Encode()
}

// Exchange Code for Token
func (c *Client) ExchangeCodeForToken(code, redirectURI string) (map[string]interface{}, error) {
	v := url.Values{}
	v.Set("grant_type", "authorization_code")
	v.Set("code", code)
	v.Set("client_id", c.ClientID)
	v.Set("client_secret", c.ClientSecret)
	v.Set("redirect_uri", redirectURI)

	req, err := http.NewRequest("POST", c.BaseURL+"/oauth/token", strings.NewReader(v.Encode()))
	if err != nil {
		return nil, err
	}
	req.Header.Set("Content-Type", "application/x-www-form-urlencoded")

	resp, err := c.HTTPClient.Do(req)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	var result map[string]interface{}
	json.NewDecoder(resp.Body).Decode(&result)
	if token, ok := result["access_token"].(string); ok {
		c.AccessToken = token
	}
	return result, nil
}

// Generate Core ID
func (c *Client) GenerateCoreID() (map[string]interface{}, error) {
	var res map[string]interface{}
	err := c.doRequest("GET", "/api/v1/core/uiid/generate", nil, &res)
	return res, err
}

// Patch Alias Data
func (c *Client) PatchAliasData(payload map[string]interface{}) (map[string]interface{}, error) {
	var res map[string]interface{}
	err := c.doRequest("PATCH", "/api/v1/aliases/data", payload, &res)
	return res, err
}

// Request Storage Node Deletion
func (c *Client) RequestNodeDeletion(aliasID, key string) (map[string]interface{}, error) {
	var res map[string]interface{}
	payload := map[string]string{"alias_id": aliasID, "key": key}
	err := c.doRequest("POST", "/api/v1/aliases/storage/request-deletion", payload, &res)
	return res, err
}

// Add Shared Bucket Member (v2.6)
func (c *Client) AddAliasMember(aliasIDStr, targetUIID, role string) (map[string]interface{}, error) {
	var res map[string]interface{}
	payload := map[string]string{"alias_id_str": aliasIDStr, "target_uiid": targetUIID, "role": role}
	err := c.doRequest("POST", "/api/v1/aliases/members", payload, &res)
	return res, err
}

// Verify Webhook Signature
func VerifyWebhookSignature(payload interface{}, signature, secret string) bool {
	var bodyBytes []byte
	switch v := payload.(type) {
	case string:
		bodyBytes = []byte(v)
	case []byte:
		bodyBytes = v
	default:
		bodyBytes, _ = json.Marshal(v)
	}

	mac := hmac.New(sha256.New, []byte(secret))
	mac.Write(bodyBytes)
	expected := hex.EncodeToString(mac.Sum(nil))
	return hmac.Equal([]byte(signature), []byte(expected))
}
