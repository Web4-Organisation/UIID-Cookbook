from uiid_client import UIIDClient

def main():
    print("=== 02. Application Management Workflow (Python) ===\n")

    client = UIIDClient()
    print("[App Mgmt] Methods available in Python SDK:")
    print(" - client.onboard_application(name='My App', redirect_uri='https://myapp.com/cb')")
    print(" - client.revoke_application(app_id='app_123')\n")

if __name__ == "__main__":
    main()
