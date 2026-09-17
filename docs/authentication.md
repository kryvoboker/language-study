[← Architecture](architecture.md) · [Back to README](../README.md) · [AI Providers →](ai-providers.md)

# Authentication

## Storefront API

Laravel Passport provides OAuth2 authentication for the Nuxt storefront. Protected API routes require a Passport bearer token and verified email status.

Nuxt exposes a server-side backend-for-frontend (BFF). The BFF stores Passport access and refresh tokens in `HttpOnly`, `SameSite=Lax` cookies; browser code never reads or persists bearer tokens. The public OAuth client uses Authorization Code with PKCE and `S256`.

Supported local flows include registration, login, password reset, signed email verification, and blocked-account checks.

### Provision the public client

After migrations, run the idempotent setup command:

```bash
php artisan passport:public-client
```

Copy the printed client ID to `NUXT_PUBLIC_PASSPORT_CLIENT_ID`. The redirect URI must exactly match the backend and frontend environment values.

Social sign-in uses Laravel Socialite. Google, GitHub, and Facebook callbacks create or link a local user and issue a single-use, 60-second exchange ticket. The ticket is exchanged for a Passport token so a bearer token is not placed in the callback URL.

## Admin panel

Filament uses Laravel's web guard and session cookies. It does not use Passport for the admin panel.

Admin access requires a verified, non-blocked user with `access_admin_panel`. The `super_admin` role is handled globally by the authorization gate. The dedicated `user` role never grants admin-panel access.

## Relevant routes

| Route | Purpose |
|-------|---------|
| `POST /api/v1/auth/register` | Register a local user |
| `POST /api/v1/auth/login` | Exchange credentials for a token |
| `POST /api/v1/auth/email/verification-notification` | Resend verification mail |
| `POST /api/v1/auth/social/exchange` | Exchange a social-login ticket |
| `GET /email/verify/{id}/{hash}` | Verify a signed email link |
| `GET /auth/social/{provider}` | Start a Socialite flow |
| `GET /auth/social/{provider}/callback` | Receive a provider callback |

## See Also

- [Admin Authorization](admin-authorization.md) — panel permissions and roles
- [API Reference](api.md) — protected and public endpoints
- [Configuration](configuration.md) — Passport and Socialite variables
