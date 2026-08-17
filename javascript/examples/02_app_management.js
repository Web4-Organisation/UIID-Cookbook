const UIIDClient = require('../src/index');

async function main() {
  console.log('=== 02. Application Management Workflow ===\n');

  const mockClient = new UIIDClient({
    accessToken: 'mock_bearer_token',
    fetch: async (url, opts) => {
      if (url.includes('/api/v1/applications') && opts.method === 'POST') {
        return {
          status: 200,
          ok: true,
          json: async () => ({
            status: 'success',
            client_id: 'app_550e8400-e29b-41d4-a716-446655440000',
            client_secret: 'sec_f47ac10b-58cc-4372-a567-0e02b2c3d479',
          }),
        };
      }
      if (url.includes('/api/v1/applications/app_123') && opts.method === 'DELETE') {
        return {
          status: 200,
          ok: true,
          json: async () => ({
            status: 'success',
            message: 'Application app_123 access revoked.',
          }),
        };
      }
      return { status: 404, ok: false, json: async () => ({ error: 'Not Found' }) };
    },
  });

  // 1. Onboard Application
  console.log('[App Mgmt] 1. Onboarding New Application...');
  const app = await mockClient.onboardApplication('My New App', 'https://myapp.com/callback');
  console.log('Onboard Response:', app, '\n');

  // 2. Revoke Application Access
  console.log('[App Mgmt] 2. Revoking Application Access for app_123...');
  const revoke = await mockClient.revokeApplication('app_123');
  console.log('Revoke Response:', revoke, '\n');
}

main().catch(console.error);
