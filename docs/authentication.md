[← Helpers and Bootstrap](helpers-and-bootstrap.md) · [Back to README](../README.md) · [AI Providers →](ai-providers.md)

# Authentication

## Storefront API

Laravel Passport provides OAuth2 authentication for the Nuxt storefront. Protected API routes require authentication; email verification is currently disabled.

Nuxt exposes a server-side backend-for-frontend (BFF). The BFF stores Passport access and refresh tokens in `HttpOnly`, `SameSite=Lax` cookies; browser code never reads or persists bearer tokens. The public OAuth client uses Authorization Code with PKCE and `S256`.

Supported local flows include registration, login, password reset, and blocked-account checks. Newly registered users can sign in immediately.

### Email verification (temporarily disabled)

Email verification has been removed temporarily and is not required for registration, login, translations, or Filament access. There is no application-wide switch; re-enabling it requires restoring the Laravel `MustVerifyEmail` contract, the signed verification and resend routes, the `verified` route middleware, and Filament's email-verification feature.

Follow the `TODO` comments in these locations when restoring the flow:

- `httpdocs/backend/app/Models/Users/User.php`
- `httpdocs/backend/routes/web.php` and `httpdocs/backend/routes/api.php`
- `httpdocs/backend/app/Providers/Filament/AdminPanelProvider.php`
- `httpdocs/backend/database/migrations/0001_01_01_000000_create_users_table.php` (legacy nullable timestamp retained for now)

Registration and password reset accept passwords from 6 through 32 characters, including special characters. Registration avatars are optional and limited to JPG and PNG images (up to 2 MB); WebP is rejected.

The credential-login endpoint issues personal access tokens. The required personal client is provisioned for the `users` provider with:

```bash
php artisan passport:client --personal --provider=users
```

Translation requests accept either a valid Passport bearer token or an authenticated Laravel web session, including a Filament admin session. Session-authenticated state-changing requests must include a valid CSRF token.

When the storefront and admin panel use different subdomains, set `SESSION_DOMAIN=.language-study.com` in the backend environment. The Nuxt BFF forwards the browser session cookie to Laravel, allowing an admin-panel session to authenticate storefront translation requests.

### Provision the public client

After migrations, run the idempotent setup command:

```bash
php artisan passport:public-client
```

Copy the printed client ID to `NUXT_PUBLIC_PASSPORT_CLIENT_ID`. The redirect URI must exactly match the backend and frontend environment values.

Social sign-in uses Laravel Socialite. The storefront login page offers Google sign-in; GitHub and Facebook are not offered there. The existing backend Socialite routes remain available for integrations that call them directly. Social callbacks create or link a local user and issue a single-use, 60-second exchange ticket. The ticket is exchanged for a Passport token so a bearer token is not placed in the callback URL.

Translation requests additionally require an active, non-blocked account. Anonymous and inactive callers receive `401` with `code: login_required`; blocked callers receive `403` with `code: account_blocked`. The storefront maps these codes to localized sign-in and support guidance.

## Admin panel

Filament uses Laravel's web guard and session cookies. It does not use Passport for the admin panel.

Admin access requires an active, non-blocked user with `access_admin_panel`; email verification is temporarily disabled and is not part of the access check. The `super_admin` role is handled globally by the authorization gate. The dedicated `user` role never grants admin-panel access.

## Relevant routes

| Route | Purpose |
|-------|---------|
| `POST /api/v1/auth/register` | Register a local user |
| `POST /api/v1/auth/login` | Exchange credentials for a token |
| `POST /api/v1/auth/social/exchange` | Exchange a social-login ticket |
| `GET /auth/social/{provider}` | Start a Socialite flow |
| `GET /auth/social/{provider}/callback` | Receive a provider callback |

## See Also

- [Admin Authorization](admin-authorization.md) — panel permissions and roles
- [API Reference](api.md) — protected and public endpoints
- [Configuration](configuration.md) — Passport and Socialite variables
