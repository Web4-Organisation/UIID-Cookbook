from uiid_client import UIIDClient

def main():
    print("=== 04. Alias Network & Shared Buckets Workflow (Python) ===\n")

    client = UIIDClient()
    print("[Alias Network] Methods available in Python SDK:")
    print(" - client.get_aliases() -> List contextual aliases")
    print(" - client.create_alias(name='Work Identity')")
    print(" - client.update_alias_status(alias_id='UIID-Alias-123', status='paused')")
    print(" - client.patch_alias_data({'alias_id': '...', 'profile.theme': 'dark', 'is_immutable': True})")
    print(" - client.request_node_deletion(alias_id='...', key='membership_id')")
    print(" - client.add_alias_member(alias_id_str='...', target_uiid='did:uiid:...', role='chat_partner') [Shared Buckets v2.6]\n")

if __name__ == "__main__":
    main()
