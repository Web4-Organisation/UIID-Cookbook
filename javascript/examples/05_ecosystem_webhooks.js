const UIIDClient = require('../src/index');

async function main() {
  console.log('=== 05. Ecosystem, Auditing & Webhooks Workflow ===\n');

  const mockClient = new UIIDClient({
    accessToken: 'mock_bearer_token',
    fetch: async (url, opts) => {
      if (url.includes('/api/v1/marketplace/apps')) {
        return {
          status: 200,
          ok: true,
          json: async () => ({
            status: 'success',
            apps: [{ id: 1, name: 'Linkspreed', category: 'Social' }],
          }),
        };
      }
      if (url.includes('/api/v1/user/badges')) {
        return {
          status: 200,
          ok: true,
          json: async () => ({
            status: 'success',
            badges: [{ badge_key: 'kyc_verified', level: 1, issued_at: '2026-01-01' }],
          }),
        };
      }
      if (url.includes('/api/v1/audit/core')) {
        return {
          status: 200,
          ok: true,
          json: async () => ({
            status: 'success',
            audit_logs: [{ action: 'LOGIN', timestamp: '2026-01-15T12:00:00Z' }],
          }),
        };
      }
      if (url.includes('/api/v1/webhooks') && opts.method === 'POST') {
        return {
          status: 200,
          ok: true,
          json: async () => ({
            status: 'success',
            secret: 'whsec_987654321_secret',
          }),
        };
      }
      return { status: 404, ok: false, json: async () => ({ error: 'Not Found' }) };
    },
  });

  // 1. Ecosystem Discovery
  console.log('[Ecosystem] 1. Discovering Marketplace Applications...');
  const marketApps = await mockClient.getMarketplaceApps();
  console.log('Marketplace Apps:', marketApps, '\n');

  // 2. User Credentials / Trust Badges
  console.log('[Trust] 2. Querying User Verified Badges...');
  const badges = await mockClient.getUserBadges();
  console.log('User Badges:', badges, '\n');

  // 3. Core Audit Logs
  console.log('[Audit] 3. Retrieving Identity Audit Activity Stream...');
  const audit = await mockClient.getCoreAuditLogs();
  console.log('Core Audit Logs:', audit, '\n');

  // 4. Webhook Subscription & Verification
  console.log('[Webhooks] 4. Subscribing to Identity Webhook Events...');
  const sub = await mockClient.subscribeWebhook('https://myapp.com/webhook', [
    'alias.data.created',
    'alias.data.updated',
  ]);
  console.log('Webhook Subscribed:', sub, '\n');

  // 5. Verify Incoming Webhook HMAC Signature
  const incomingPayload = { event: 'alias.data.updated', alias_id: 'UIID-Alias-80BB' };
  const secret = sub.secret || 'whsec_987654321_secret';
  const crypto = require('crypto');
  const signature = crypto.createHmac('sha256', secret).update(JSON.stringify(incomingPayload)).digest('hex');

  const isValid = UIIDClient.verifyWebhookSignature(incomingPayload, signature, secret);
  console.log('[Webhooks] 5. HMAC Signature Verification Check:', isValid ? 'PASSED' : 'FAILED', '\n');
}

main().catch(console.error);
