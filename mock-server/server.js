const express = require('express');
const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// 01. OIDC Endpoints
app.get('/oauth/authorize', (req, res) => {
  const { client_id, redirect_uri, response_type, scope } = req.query;
  res.redirect(`${redirect_uri}?code=mock_authorization_code_12345`);
});

app.post('/oauth/token', (req, res) => {
  res.json({
    access_token: 'mock_access_token_eyJhbGciOiJSUzI1NiIs...',
    token_type: 'Bearer',
    expires_in: 3600,
    refresh_token: 'ref_987654321',
    scope: req.body.scope || 'openid profile email',
  });
});

app.get('/oauth/userinfo', (req, res) => {
  res.json({
    sub: 'did:uiid:9D1B-3239-5D1D-8399',
    name: 'Anonymous UIID User',
    email: 'user@example.com',
    email_verified: true,
    uiid_core_id: 'did:uiid:9D1B-3239-5D1D-8399',
  });
});

app.get('/.well-known/openid-configuration', (req, res) => {
  res.json({
    issuer: 'https://uiid.linkspreed.com',
    authorization_endpoint: 'https://uiid.linkspreed.com/oauth/authorize',
    token_endpoint: 'https://uiid.linkspreed.com/oauth/token',
    userinfo_endpoint: 'https://uiid.linkspreed.com/oauth/userinfo',
    jwks_uri: 'https://uiid.linkspreed.com/.well-known/jwks.json',
  });
});

app.get('/.well-known/jwks.json', (req, res) => {
  res.json({
    keys: [
      {
        kty: 'RSA',
        use: 'sig',
        kid: 'uiid-key-1',
        alg: 'RS256',
        n: 'mock_rsa_n_value',
        e: 'AQAB',
      },
    ],
  });
});

// 02. App Management
app.post('/api/v1/applications', (req, res) => {
  res.json({
    status: 'success',
    client_id: 'app_550e8400-e29b-41d4-a716-446655440000',
    client_secret: 'sec_f47ac10b-58cc-4372-a567-0e02b2c3d479',
  });
});

app.delete('/api/v1/applications/:id', (req, res) => {
  res.json({
    status: 'success',
    message: `Application ${req.params.id} access revoked.`,
  });
});

// 03. Core ID API
app.get('/api/v1/core/uiid/generate', (req, res) => {
  res.json({ uiid: 'did:uiid:80BB-59CE-1B90-4427' });
});

app.get('/api/v1/core/kyc/status', (req, res) => {
  res.json({
    kyc_status: 'verified',
    trust_score: 85,
    last_audit: '2026-01-15',
  });
});

app.post('/api/v1/core/data', (req, res) => {
  res.json({ status: 'success', message: 'Identity node updated.' });
});

app.get('/api/v1/core/applications', (req, res) => {
  res.json({
    status: 'success',
    applications: [
      { id: 1, name: 'Linkspreed App', client_id: 'd2e19987...', created_at: '2026-01-01' },
    ],
  });
});

// 04. Alias Network & Shared Buckets
app.get('/api/v1/aliases', (req, res) => {
  res.json({
    status: 'success',
    aliases: [
      { alias_id_str: 'UIID-Alias-80BB', alias_name: 'Work Identity', status: 'active' },
    ],
  });
});

app.post('/api/v1/aliases/create', (req, res) => {
  res.json({
    status: 'success',
    alias: { alias_id_str: 'UIID-Alias-80BB', alias_name: req.body.name || 'Profile' },
  });
});

app.put('/api/v1/aliases/:id/status', (req, res) => {
  res.json({
    status: 'success',
    message: 'Alias status updated successfully.',
    new_status: req.body.status,
  });
});

app.get('/api/v1/aliases/data/:id', (req, res) => {
  res.json({
    status: 'success',
    data: {
      profile: { name: 'Amy', theme: 'dark' },
      settings: { notifications: 'enabled' },
    },
  });
});

app.post('/api/v1/aliases/data', (req, res) => {
  res.json({ status: 'success', message: 'Alias data updated successfully.' });
});

app.patch('/api/v1/aliases/data', (req, res) => {
  res.json({ status: 'success', message: 'Alias node updated via dot-notation.' });
});

app.post('/api/v1/aliases/storage/request-deletion', (req, res) => {
  res.json({ status: 'success', message: 'Deletion requested. The provider has been notified.' });
});

app.delete('/api/v1/aliases/data', (req, res) => {
  res.json({ status: 'success', message: 'Key removed from alias store.' });
});

app.delete('/api/v1/aliases/:id', (req, res) => {
  res.json({ status: 'success', message: 'Alias purged from network.' });
});

app.post('/api/v1/aliases/members', (req, res) => {
  res.json({ status: 'success', message: 'Collaborator added to shared bucket.' });
});

app.get('/api/v1/aliases/members/:alias_id', (req, res) => {
  res.json({
    status: 'success',
    members: [
      { uiid: 'did:uiid:80BB-59CE', role: 'owner', added_at: '2026-01-01' },
      { uiid: 'did:uiid:9D1B-3239', role: 'chat_partner', added_at: '2026-01-15' },
    ],
  });
});

// 05. Ecosystem & Trust
app.get('/api/v1/marketplace/apps', (req, res) => {
  res.json({
    status: 'success',
    apps: [{ id: 1, name: 'Linkspreed', url: 'https://linkspreed.com', category: 'Social' }],
  });
});

app.get('/api/v1/user/badges', (req, res) => {
  res.json({
    status: 'success',
    badges: [{ badge_key: 'kyc_verified', level: 1, issued_at: '2026-01-01' }],
  });
});

// 06. Auditing
app.get('/api/v1/audit/core', (req, res) => {
  res.json({
    status: 'success',
    audit_logs: [{ action: 'LOGIN', timestamp: new Date().toISOString() }],
  });
});

// 07. Local Integration
app.get('/api/v1/auth/check', (req, res) => {
  res.json({ logged_in: true, uiid: 'did:uiid:80BB-59CE-1B90-4427' });
});

// 08. Webhooks
app.post('/api/v1/webhooks', (req, res) => {
  res.json({ status: 'success', secret: 'whsec_mock_secret_987654321' });
});

app.listen(PORT, () => {
  console.log(`UIID API v1 Local Mock Server running on http://localhost:${PORT}`);
});
