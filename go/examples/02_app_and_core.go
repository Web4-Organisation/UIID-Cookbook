package main

import (
	"fmt"

	"uiid.linkspreed.com/cookbook/go/uiid"
)

func main() {
	fmt.Println("=== 02. Application Management & Core ID (Go) ===")

	_ = uiid.NewClient("app_550e8400", "sec_f47ac10b", "")

	fmt.Println("[App Mgmt & Core ID] Available Go Client Methods:")
	fmt.Println(" - client.GenerateCoreID()")
	fmt.Println(" - client.PatchAliasData(payload)")
	fmt.Println(" - client.RequestNodeDeletion(aliasID, key)")
	fmt.Println(" - client.AddAliasMember(aliasIDStr, targetUIID, role)")
}
