package main

import (
	"fmt"

	"uiid.linkspreed.com/cookbook/go/uiid"
)

func main() {
	fmt.Println("=== 01. OIDC & Authentication Workflow (Go) ===")

	client := uiid.NewClient("app_550e8400", "sec_f47ac10b", "https://uiid.linkspreed.com")

	authURL := client.GetAuthorizeURL("https://myapp.com/callback", "openid profile email alias:read:public")
	fmt.Printf("[OIDC] 1. Authorize URL:\n  %s\n\n", authURL)

	fmt.Println("[OIDC] Available Go Client Methods:")
	fmt.Println(" - client.ExchangeCodeForToken(code, redirectURI)")
	fmt.Println(" - client.GetAuthorizeURL(redirectURI, scope)")
}
