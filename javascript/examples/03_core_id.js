const UIIDClient = require('../src/index');

async function main() {
  console.log('=== 03. Core ID API Workflow ===\n');

  const mockClient = new UIIDClient({
    accessToken: 'mock_bearer_token',
    fetch: async (url, opts) => {
      if (url.includes('/api/v1/core/uiid/generate')) {
        return {
          status: 200,
          ok: true,
          json: async () => ({ uiid: 'did:uiid:80BB-59CE-1B90-4427' }),
        };
      }
      if (url.includes('/api/v1/core/kyc/status')) {
        return {
          status: 200,
          ok: true,
          json: async () => ({
            kyc_status: 'verified',
            trust_score: 85,
            last_audit: '2026-01-15',
          }),
        };
      }
      if (url.includes('/api/v1/core/data') && opts.method === 'POST') {
        return {
          status: 200,
          ok: true,
          json: async () => ({ status: 'success', message: 'Identity node updated.' }),
        };
      }
      if (url.includes('/api/v1/core/applications')) {
        return {
          status: 200,
          ok: true,
          json: async () => ({
            status: 'success',
            applications: [
              { id: 1, name: 'Linkspreed App', client_id: 'd2e19987...', created_at: '2026-01-01' },
            ],
          }),
        };
      }
      return { status: 404, ok: false, json: async () => ({ error: 'Not Found' }) };
    },
  });

  // 1. Generate Public Core UIID
  console.log('[Core ID] 1. Generating Public Core UIID Anchor...');
  const gen = await mockClient.generateCoreID();
  console.log('Generated UIID:', gen, '\n');

  // 2. Query KYC / Identity Trust Level
  console.log('[Core ID] 2. Querying KYC & Trust Score...');
  const kyc = await mockClient.getKycStatus();
  console.log('KYC Status:', kyc, '\n');

  // 3. Store Encrypted Data Node
  console.log('[Core ID] 3. Storing Immutable Encrypted Application Data...');
  const store = await mockClient.storeCoreData('app_config', 'encrypted_payload_string', false, true);
  console.log('Store Response:', store, '\n');

  // 4. Auth Audit - Authorized Applications
  console.log('[Core ID] 4. Fetching List of Authorized Third-Party Apps...');
  const apps = await mockClient.getAuthorizedApplications();
  console.log('Authorized Apps:', apps, '\n');
}

main().catch(console.error);
