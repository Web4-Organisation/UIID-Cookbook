const UIIDClient = require('../src/index');

async function main() {
  console.log('=== 01. OIDC & Authentication Workflow ===\n');

  const client = new UIIDClient({
    clientId: 'app_550e8400_client_id',
    clientSecret: 'sec_f47ac10b_client_secret',
    baseUrl: 'https://uiid.linkspreed.com',
  });

  // 1. Authorize URL Generation
  const authUrl = client.getAuthorizeUrl({
    redirectUri: 'https://myapp.com/callback',
    scope: 'openid profile email alias:read:public',
  });
  console.log('[OIDC] 1. Redirect User to Authorize URL:\n', authUrl, '\n');

  // Mocking API responses for runnable offline presentation
  const mockClient = new UIIDClient({
    clientId: 'app_550e8400_client_id',
    clientSecret: 'sec_f47ac10b_client_secret',
    fetch: async (url, opts) => {
      if (url.includes('/oauth/token')) {
        return {
          status: 200,
          ok: true,
          json: async () => ({
            access_token: 'eyJhbGciOiJSUzI1NiIsI...mock_access_token',
            token_type: 'Bearer',
            expires_in: 3600,
            refresh_token: 'ref_987654321',
            scope: 'openid profile email',
          }),
        };
      }
      if (url.includes('/oauth/userinfo')) {
        return {
          status: 200,
          ok: true,
          json: async () => ({
            sub: 'did:uiid:9D1B-3239-5D1D-8399',
            name: 'Anonymous UIID User',
            email: 'user@example.com',
            email_verified: true,
            uiid_core_id: 'did:uiid:9D1B-3239-5D1D-8399',
          }),
        };
      }
      if (url.includes('/.well-known/openid-configuration')) {
        return {
          status: 200,
          ok: true,
          json: async () => ({
            issuer: 'https://uiid.linkspreed.com',
            authorization_endpoint: 'https://uiid.linkspreed.com/oauth/authorize',
            token_endpoint: 'https://uiid.linkspreed.com/oauth/token',
            userinfo_endpoint: 'https://uiid.linkspreed.com/oauth/userinfo',
            jwks_uri: 'https://uiid.linkspreed.com/.well-known/jwks.json',
          }),
        };
      }
      return { status: 404, ok: false, json: async () => ({ error: 'Not Found' }) };
    },
  });

  // 2. Token Exchange
  console.log('[OIDC] 2. Exchanging Authorization Code for Tokens...');
  const tokenPayload = await mockClient.exchangeCodeForToken('CODE_FROM_URL', 'https://myapp.com/callback');
  console.log('Token Payload:', tokenPayload, '\n');

  // 3. Fetch UserInfo
  console.log('[OIDC] 3. Fetching UserInfo Claims...');
  const userInfo = await mockClient.getUserInfo();
  console.log('UserInfo Payload:', userInfo, '\n');

  // 4. Token Refresh Flow
  console.log('[OIDC] 4. Refreshing Access Token using Refresh Token...');
  const refreshedToken = await mockClient.refreshToken(tokenPayload.refresh_token);
  console.log('Refreshed Token Payload:', refreshedToken, '\n');

  // 5. OpenID Discovery Config
  console.log('[OIDC] 5. Fetching OpenID Discovery Configuration...');
  const oidcConfig = await mockClient.getOidcDiscovery();
  console.log('OIDC Discovery Config:', oidcConfig, '\n');
}

main().catch(console.error);
