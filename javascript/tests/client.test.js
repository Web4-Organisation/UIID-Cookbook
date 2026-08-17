const test = require('node:test');
const assert = require('node:assert');
const UIIDClient = require('../src/index');

test('UIIDClient - OIDC Authorize URL Generation', () => {
  const client = new UIIDClient({
    clientId: 'test_client_123',
    baseUrl: 'https://uiid.linkspreed.com',
  });

  const url = client.getAuthorizeUrl({ redirectUri: 'https://app.com/cb' });
  assert.ok(url.includes('client_id=test_client_123'));
  assert.ok(url.includes('redirect_uri=https%3A%2F%2Fapp.com%2Fcb'));
});

test('UIIDClient - Mock Token Exchange', async () => {
  const mockFetch = async (url, options) => {
    assert.strictEqual(url, 'https://uiid.linkspreed.com/oauth/token');
    return {
      status: 200,
      ok: true,
      json: async () => ({
        access_token: 'mock_access_token_abc',
        token_type: 'Bearer',
        expires_in: 3600,
        refresh_token: 'ref_12345',
      }),
    };
  };

  const client = new UIIDClient({
    clientId: 'test_client',
    clientSecret: 'test_secret',
    fetch: mockFetch,
  });

  const res = await client.exchangeCodeForToken('code_123', 'https://app.com/cb');
  assert.strictEqual(res.access_token, 'mock_access_token_abc');
  assert.strictEqual(client.accessToken, 'mock_access_token_abc');
});

test('UIIDClient - Alias Data Patch & Deletion Request', async () => {
  const mockFetch = async (url, options) => {
    if (url.includes('/api/v1/aliases/data') && options.method === 'PATCH') {
      return {
        status: 200,
        ok: true,
        json: async () => ({ status: 'success', message: 'Patched successfully' }),
      };
    }
    if (url.includes('/api/v1/aliases/storage/request-deletion')) {
      return {
        status: 200,
        ok: true,
        json: async () => ({ status: 'success', message: 'Deletion requested' }),
      };
    }
    return { status: 400, ok: false, json: async () => ({ status: 'error' }) };
  };

  const client = new UIIDClient({ fetch: mockFetch });
  client.setAccessToken('mock_token');

  const patchRes = await client.patchAliasData({
    alias_id: 'UIID-Alias-123',
    'profile.theme': 'dark',
    is_immutable: true,
  });
  assert.strictEqual(patchRes.status, 'success');

  const delRes = await client.requestNodeDeletion('UIID-Alias-123', 'membership_id');
  assert.strictEqual(delRes.status, 'success');
});

test('UIIDClient - Shared Buckets & Collaborators', async () => {
  const mockFetch = async (url, options) => {
    if (url.includes('/api/v1/aliases/members') && options.method === 'POST') {
      return {
        status: 200,
        ok: true,
        json: async () => ({ status: 'success', message: 'Member added' }),
      };
    }
    return { status: 400, ok: false, json: async () => ({ status: 'error' }) };
  };

  const client = new UIIDClient({ fetch: mockFetch });
  const memberRes = await client.addAliasMember('UIID-Alias-123', 'did:uiid:80BB-59CE', 'chat_partner');
  assert.strictEqual(memberRes.status, 'success');
});

test('UIIDClient - Webhook HMAC Signature Verification', () => {
  const payload = { event: 'alias.data.updated', alias_id: 'UIID-Alias-99' };
  const secret = 'webhook_secret_key';
  const crypto = require('crypto');
  const validSig = crypto.createHmac('sha256', secret).update(JSON.stringify(payload)).digest('hex');

  assert.strictEqual(UIIDClient.verifyWebhookSignature(payload, validSig, secret), true);
  assert.strictEqual(UIIDClient.verifyWebhookSignature(payload, 'invalid_sig', secret), false);
});
