// NextAuth.js / Auth.js Custom UIID Provider Configuration Example
// Usage in Next.js: app/api/auth/[...nextauth]/route.ts

import UIIDClient from 'uiid-cookbook-js';

export const UIIDProvider = {
  id: "uiid",
  name: "UIID (Universal Integrated Identity)",
  type: "oauth",
  wellKnown: "https://uiid.linkspreed.com/.well-known/openid-configuration",
  authorization: { params: { scope: "openid profile email alias:read:public" } },
  idToken: true,
  checks: ["pkce", "state"],
  profile(profile) {
    return {
      id: profile.sub || profile.uiid_core_id,
      name: profile.name || "Anonymous UIID User",
      email: profile.email,
      image: null,
    };
  },
  clientId: process.env.UIID_CLIENT_ID,
  clientSecret: process.env.UIID_CLIENT_SECRET,
};

// Express.js UIID Webhook Middleware Example
export function expressUIIDWebhookMiddleware(webhookSecret) {
  return (req, res, next) => {
    const signature = req.headers['x-uiid-signature'];
    if (!signature) {
      return res.status(401).json({ error: 'Missing X-UIID-Signature header' });
    }

    const isValid = UIIDClient.verifyWebhookSignature(req.body, signature, webhookSecret);
    if (!isValid) {
      return res.status(403).json({ error: 'Invalid HMAC signature' });
    }

    next();
  };
}
