const UIIDClient = require('../src/index');

async function main() {
  console.log('=== 04. Alias Network & Shared Buckets Workflow ===\n');

  const mockClient = new UIIDClient({
    accessToken: 'mock_bearer_token',
    fetch: async (url, opts) => {
      if (url.includes('/api/v1/aliases/create')) {
        return {
          status: 200,
          ok: true,
          json: async () => ({
            status: 'success',
            alias: { alias_id_str: 'UIID-Alias-80BB', alias_name: 'Work Identity' },
          }),
        };
      }
      if (url.includes('/api/v1/aliases/data') && opts.method === 'PATCH') {
        return {
          status: 200,
          ok: true,
          json: async () => ({ status: 'success', message: 'Alias node updated via dot-notation.' }),
        };
      }
      if (url.includes('/api/v1/aliases/storage/request-deletion')) {
        return {
          status: 200,
          ok: true,
          json: async () => ({ status: 'success', message: 'Deletion requested. Provider notified.' }),
        };
      }
      if (url.includes('/api/v1/aliases/members') && opts.method === 'POST') {
        return {
          status: 200,
          ok: true,
          json: async () => ({ status: 'success', message: 'Collaborator added to shared bucket.' }),
        };
      }
      return { status: 404, ok: false, json: async () => ({ error: 'Not Found' }) };
    },
  });

  // 1. Spawn Alias
  console.log('[Alias] 1. Spawning Contextual Alias Identity...');
  const alias = await mockClient.createAlias('Work Identity');
  console.log('Alias Created:', alias, '\n');

  // 2. Patch Alias Data (Dot-Notation & Lock Immutable Node)
  console.log('[Alias] 2. Patching Alias Node Data with Immutable Lock...');
  const patch = await mockClient.patchAliasData({
    alias_id: 'UIID-Alias-80BB',
    'profile.theme': 'dark',
    is_immutable: true,
  });
  console.log('Patch Response:', patch, '\n');

  // 3. User Deletion Request for Immutable Node
  console.log('[Alias] 3. Submitting Deletion Request for Immutable Node...');
  const delReq = await mockClient.requestNodeDeletion('UIID-Alias-80BB', 'membership_id');
  console.log('Deletion Request Response:', delReq, '\n');

  // 4. Shared Buckets & Collaboration (v2.6)
  console.log('[Alias] 4. Adding Collaborative Member to Shared Bucket...');
  const member = await mockClient.addAliasMember('UIID-Alias-80BB', 'did:uiid:80BB-59CE-1B90-4427', 'chat_partner');
  console.log('Member Added Response:', member, '\n');
}

main().catch(console.error);
