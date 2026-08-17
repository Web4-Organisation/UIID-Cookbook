package main

import (
	"crypto/hmac"
	"crypto/sha256"
	"encoding/hex"
	"encoding/json"
	"fmt"

	"uiid.linkspreed.com/cookbook/go/uiid"
)

func main() {
	fmt.Println("=== 03. Webhooks & HMAC Verification (Go) ===")

	payload := map[string]string{"event": "alias.data.updated", "alias_id": "UIID-Alias-80BB"}
	secret := "whsec_secret_123"

	jsonBytes, _ := json.Marshal(payload)
	mac := hmac.New(sha256.New, []byte(secret))
	mac.Write(jsonBytes)
	signature := hex.EncodeToString(mac.Sum(nil))

	isValid := uiid.VerifyWebhookSignature(payload, signature, secret)
	fmt.Printf("[Webhooks] HMAC Signature Verification: %v\n", isValid)
}
