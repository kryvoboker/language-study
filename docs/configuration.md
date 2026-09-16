[← Admin Authorization](admin-authorization.md) · [Back to README](../README.md) · [API Reference →](api.md)

# Configuration

Configuration is split between `httpdocs/backend/.env` and `httpdocs/frontend/.env`. Development service values also come from `.docker/dev/env/.env` and `.docker/dev/env/.env.mariadb`.

## Backend variables

| Variable | Purpose | Example/default |
|----------|---------|-----------------|
| `APP_NAME` | Application name | `NativeLens` |
| `APP_ENV` | Runtime environment | `local` |
| `APP_KEY` | Laravel encryption key | Generate with `php artisan key:generate` |
| `APP_DEBUG` | Local debugging | `true` locally |
| `APP_URL` | Backend public URL | `http://localhost:8080` |
| `FRONTEND_URL` | Storefront URL used by verification/social redirects | `http://localhost:8080` |
| `CORS_ALLOWED_ORIGINS` | Comma-separated browser origins | `http://localhost:8080,http://localhost:3000` |
| `NEW_STORAGE_PATH` | External Laravel storage path | `/var/www/storage` |
| `DB_CONNECTION` | Database driver | `mysql` |
| `DB_HOST` / `DB_PORT` | Database address | `mariadb` / `3306` |
| `DB_DATABASE` | Database name | `nativelens` |
| `DB_USERNAME` / `DB_PASSWORD` | Database credentials | Development-only example values |
| `CACHE_STORE` | Cache backend | `redis` |
| `QUEUE_CONNECTION` | Queue backend | `redis` |
| `SESSION_DRIVER` | Session backend | `redis` |
| `REDIS_HOST` / `REDIS_PORT` | Redis address | `redis` / `6379` |
| `MAIL_*` | Mail transport and sender | Mailpit on port `1025` locally |
| `GOOGLE_*` | Google Socialite credentials | Empty until configured |
| `GITHUB_*` | GitHub Socialite credentials | Empty until configured |
| `FACEBOOK_*` | Facebook Socialite credentials | Empty until configured |

The complete list is in [`httpdocs/backend/.env.example`](../httpdocs/backend/.env.example).

## Frontend variables

| Variable | Purpose | Default |
|----------|---------|---------|
| `NUXT_PUBLIC_API_BASE` | Browser API base path | `/api/v1` |
| `NUXT_PUBLIC_BACKEND_ORIGIN` | Backend origin for auth redirects | `http://localhost:8080` |
| `NUXT_PUBLIC_PASSPORT_CLIENT_ID` | Passport client identifier | Empty |
| `NUXT_PUBLIC_PASSPORT_REDIRECT_URI` | OAuth callback URL | `http://localhost:8080/auth/callback` |
| `NUXT_PUBLIC_PASSPORT_CLIENT_NAME` | Human-readable public client label | `NativeLens Storefront` |
| `NUXT_API_INTERNAL_BASE` | Server-side API base URL | `http://nginx/api/v1` |

See [`httpdocs/frontend/.env.example`](../httpdocs/frontend/.env.example) for the authoritative template.

## Runtime defaults

Nuxt uses English as the default locale and supports English, Ukrainian, and Russian. The workbench defaults to English source and Ukrainian target languages. Its debounce interval is 650 ms and polling interval is approximately 350 ms.

## Secret handling

- Do not commit real `.env` files, private keys, or provider credentials.
- Keep `APP_KEY`, Passport keys, and social/provider secrets out of client-exposed variables.
- Store AI provider credentials through the encrypted backend setting.
- Use production-specific URLs and disable `APP_DEBUG` outside local development.
- Never put a Passport client secret in Nuxt variables; the browser client is intentionally public.

## See Also

- [Getting Started](getting-started.md) — copying environment templates
- [Authentication](authentication.md) — Passport and Socialite behavior
- [Deployment](deployment.md) — production configuration guidance
