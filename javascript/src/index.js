const crypto = require('crypto');

class UIIDClient {
  constructor(options = {}) {
    this.baseUrl = options.baseUrl || 'https://uiid.linkspreed.com';
    this.clientId = options.clientId || '';
    this.clientSecret = options.clientSecret || '';
    this.accessToken = options.accessToken || '';
    this.fetchImpl = options.fetch || globalThis.fetch;
  }

  // Set bearer token manually
  setAccessToken(token) {
    this.accessToken = token;
  }

  // Internal helper for HTTP requests
  async _request(endpoint, options = {}) {
    const url = endpoint.startsWith('http') ? endpoint : `${this.baseUrl}${endpoint}`;
    const headers = {
      'Content-Type': 'application/json',
      ...options.headers,
    };

    if (this.accessToken && !headers['Authorization']) {
      headers['Authorization'] = `Bearer ${this.accessToken}`;
    }

    const config = {
      method: options.method || 'GET',
      headers,
    };

    if (options.body) {
      config.body = typeof options.body === 'string' ? options.body : JSON.stringify(options.body);
    }

    if (options.credentials) {
      config.credentials = options.credentials;
    }

    const response = await this.fetchImpl(url, config);
    const data = await response.json();
    return { status: response.status, ok: response.ok, data };
  }

  // --- 02. Authentication (OIDC) ---

  getAuthorizeUrl(params = {}) {
    const query = new URLSearchParams({
      client_id: params.clientId || this.clientId,
      redirect_uri: params.redirectUri || '',
      response_type: 'code',
      scope: params.scope || 'openid profile email alias:read:public',
    });
    return `${this.baseUrl}/oauth/authorize?${query.toString()}`;
  }

  async exchangeCodeForToken(code, redirectUri) {
    const body = new URLSearchParams({
      grant_type: 'authorization_code',
      code,
      client_id: this.clientId,
      client_secret: this.clientSecret,
      redirect_uri: redirectUri,
    });

    const res = await this._request('/oauth/token', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    });

    if (res.data && res.data.access_token) {
      this.accessToken = res.data.access_token;
    }
    return res.data;
  }

  async refreshToken(refreshToken) {
    const body = new URLSearchParams({
      grant_type: 'refresh_token',
      refresh_token: refreshToken,
      client_id: this.clientId,
      client_secret: this.clientSecret,
    });

    const res = await this._request('/oauth/token', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    });

    if (res.data && res.data.access_token) {
      this.accessToken = res.data.access_token;
    }
    return res.data;
  }

  async getUserInfo() {
    const res = await this._request('/oauth/userinfo');
    return res.data;
  }

  async getOidcDiscovery() {
    const res = await this._request('/.well-known/openid-configuration');
    return res.data;
  }

  async getJwks() {
    const res = await this._request('/.well-known/jwks.json');
    return res.data;
  }

  // --- 03. App Management ---

  async onboardApplication(name, redirectUri) {
    const res = await this._request('/api/v1/applications', {
      method: 'POST',
      body: { name, redirect_uri: redirectUri },
    });
    return res.data;
  }

  async revokeApplication(appId) {
    const res = await this._request(`/api/v1/applications/${appId}`, {
      method: 'DELETE',
    });
    return res.data;
  }

  // --- 04. Core ID API ---

  async generateCoreID() {
    const res = await this._request('/api/v1/core/uiid/generate');
    return res.data;
  }

  async getKycStatus() {
    const res = await this._request('/api/v1/core/kyc/status');
    return res.data;
  }

  async storeCoreData(key, value, isPublic = false, isImmutable = false) {
    const res = await this._request('/api/v1/core/data', {
      method: 'POST',
      body: { key, value, is_public: isPublic, is_immutable: isImmutable },
    });
    return res.data;
  }

  async getAuthorizedApplications() {
    const res = await this._request('/api/v1/core/applications');
    return res.data;
  }

  // --- 05. Alias Network ---

  async getAliases() {
    const res = await this._request('/api/v1/aliases');
    return res.data;
  }

  async createAlias(name) {
    const res = await this._request('/api/v1/aliases/create', {
      method: 'POST',
      body: { name },
    });
    return res.data;
  }

  async updateAliasStatus(aliasId, status, secretKey = null) {
    const payload = { status };
    if (secretKey) payload.key = secretKey;
    const res = await this._request(`/api/v1/aliases/${aliasId}/status`, {
      method: 'PUT',
      body: payload,
    });
    return res.data;
  }

  async getAliasData(aliasId) {
    const res = await this._request(`/api/v1/aliases/data/${aliasId}`);
    return res.data;
  }

  async overwriteAliasData(aliasId, data) {
    const res = await this._request('/api/v1/aliases/data', {
      method: 'POST',
      body: { alias_id: aliasId, data },
    });
    return res.data;
  }

  async patchAliasData(payload) {
    const res = await this._request('/api/v1/aliases/data', {
      method: 'PATCH',
      body: payload,
    });
    return res.data;
  }

  async requestNodeDeletion(aliasId, key) {
    const res = await this._request('/api/v1/aliases/storage/request-deletion', {
      method: 'POST',
      body: { alias_id: aliasId, key },
    });
    return res.data;
  }

  async removeAliasKey(aliasId, key) {
    const res = await this._request('/api/v1/aliases/data', {
      method: 'DELETE',
      body: { alias_id: aliasId, key },
    });
    return res.data;
  }

  async purgeAlias(aliasId) {
    const res = await this._request(`/api/v1/aliases/${aliasId}`, {
      method: 'DELETE',
    });
    return res.data;
  }

  // --- Shared Buckets (v2.6) ---

  async addAliasMember(aliasIdStr, targetUiid, role) {
    const res = await this._request('/api/v1/aliases/members', {
      method: 'POST',
      body: { alias_id_str: aliasIdStr, target_uiid: targetUiid, role },
    });
    return res.data;
  }

  async getAliasMembers(aliasId) {
    const res = await this._request(`/api/v1/aliases/members/${aliasId}`);
    return res.data;
  }

  // --- 06. Ecosystem & Trust ---

  async getMarketplaceApps() {
    const res = await this._request('/api/v1/marketplace/apps');
    return res.data;
  }

  async getMarketplaceAppDetails(appId) {
    const res = await this._request(`/api/v1/marketplace/apps/${appId}`);
    return res.data;
  }

  async getBadges() {
    const res = await this._request('/api/v1/badges');
    return res.data;
  }

  async getUserBadges() {
    const res = await this._request('/api/v1/user/badges');
    return res.data;
  }

  // --- 07. Auditing ---

  async getCoreAuditLogs() {
    const res = await this._request('/api/v1/audit/core');
    return res.data;
  }

  async getAliasAuditLogs(aliasId) {
    const res = await this._request(`/api/v1/audit/alias?alias_id=${aliasId}`);
    return res.data;
  }

  // --- 08. Local Integration & Smart Tags ---

  async checkAuthStatus() {
    const res = await this._request('/api/v1/auth/check', {
      credentials: 'include',
    });
    return res.data;
  }

  // --- 10. Webhooks ---

  async subscribeWebhook(url, events, aliasId = null) {
    const body = { url, events };
    if (aliasId) body.alias_id = aliasId;
    const res = await this._request('/api/v1/webhooks', {
      method: 'POST',
      body,
    });
    return res.data;
  }

  static verifyWebhookSignature(payload, signature, webhookSecret) {
    const hmac = crypto.createHmac('sha256', webhookSecret);
    const bodyStr = typeof payload === 'string' ? payload : JSON.stringify(payload);
    const digest = hmac.update(bodyStr).digest('hex');
    return signature === digest;
  }
}

module.exports = UIIDClient;
