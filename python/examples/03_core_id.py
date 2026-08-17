from uiid_client import UIIDClient

def main():
    print("=== 03. Core ID API Workflow (Python) ===\n")

    client = UIIDClient()
    print("[Core ID] Methods available in Python SDK:")
    print(" - client.generate_core_id() -> Generate public identity string")
    print(" - client.get_kyc_status() -> Fetch verification level & trust score")
    print(" - client.store_core_data(key='config', value='encrypted', is_public=False, is_immutable=True)")
    print(" - client.get_authorized_applications() -> Retrieve authorized third-party app list\n")

if __name__ == "__main__":
    main()
