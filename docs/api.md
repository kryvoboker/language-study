[← Configuration](configuration.md) · [Back to README](../README.md) · [Deployment →](deployment.md)

# API Reference

The API is versioned under `/api/v1`. The complete OpenAPI 3.1 contract is [`openapi.yaml`](openapi.yaml), and Laravel renders it at `/docs/api`.

## Authentication

Protected routes require authentication but do not require email verification. Email verification is temporarily disabled; consult the `TODO` comments in the User model, API/web routes, and Filament panel provider before restoring it. Most protected API routes use a Passport bearer token; translation routes also accept an authenticated Laravel web session. Public authentication routes are rate-limited separately.

```http
Authorization: Bearer <passport-access-token>

For the Nuxt storefront, callers use same-origin BFF routes; the BFF adds this header server-side from an HttpOnly cookie.
```

## Authentication endpoints

| Method | Endpoint | Auth | Purpose |
|--------|----------|------|---------|
| `POST` | `/auth/register` | Public | Register a user |
| `POST` | `/auth/login` | Public | Issue a Passport token |
| `POST` | `/auth/social/exchange` | Public | Exchange a one-time social ticket |
| `POST` | `/auth/forgot-password` | Public | Send a reset link |
| `POST` | `/auth/reset-password` | Public | Set a new password |

## User and translation endpoints

Translation endpoints accept either a Passport bearer token or an authenticated Laravel web session (including the Filament admin session). The user must be active and not be blocked. Session-authenticated state-changing requests must include a valid CSRF token.

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `GET` | `/me` | Return the authenticated user |
| `POST` | `/translation-requests` | Create a translation request |
| `GET` | `/translation-requests/{translation_request}` | Read status or result |
| `DELETE` | `/translation-requests/{translation_request}` | Cancel a request |

Translation access errors use stable `code` values. Anonymous and inactive users receive `401` with `login_required`; the storefront asks them to sign in. Blocked users receive `403` with `account_blocked`; the storefront explains the restriction and directs them to technical support. These checks apply to creating, polling, and cancelling translation requests.

## Contact requests

`POST /api/v1/contact-requests` accepts guests and authenticated users, including inactive and blocked accounts. Guest requests require `first_name`, `last_name`, and `email`; authenticated requests use the account identity and reject client-supplied identity fields. Every request requires a `message` (up to 10,000 characters) and may include up to 10 JPEG or PNG images, each no larger than 5 MB. Attachments are stored on Laravel's private local disk.

The endpoint returns `202 Accepted` after persisting the request and attachments. Telegram delivery happens asynchronously in the backend; the storefront reports durable acceptance, not Telegram delivery. Blocked-user submissions are marked in the stored request and explicitly labeled in Telegram. Configure `CONTACT_TELEGRAM_BOT_TOKEN` and `CONTACT_TELEGRAM_CHAT_ID` in the backend environment. Never expose these values to Nuxt or browser code. Contact requests are rate-limited to 5 submissions per minute.

## Register a user

`POST /api/v1/auth/register` accepts `multipart/form-data`. `name`, `email`, `password`, and `password_confirmation` are required; passwords must contain 6–32 characters, may include special characters, and require a matching confirmation. Password resets use the same length limits. `avatar` is optional and accepts JPG or PNG images up to 2 MB. Uploaded avatars are stored under the date-resolved `app.user_dir` path in `images/avatar/`.

## Create a translation request

```http
POST /api/v1/translation-requests
Content-Type: application/json
Authorization: Bearer <passport-access-token>

{
  "source_text": "on my own",
  "source_language": "en",
  "target_language": "uk"
}
```

The endpoint returns `202 Accepted` with a request resource. Poll it until `completed`, `failed`, or `cancelled`.

```json
{
  "id": "00000000-0000-0000-0000-000000000000",
  "status": "completed",
  "translation": "самостійно",
  "natural_usage": {
    "expression": "on my own",
    "example": "I learned how to build this website on my own."
  },
  "source_corrected": null,
  "issues": []
}
```

`natural_usage` is `null` when the source text is a complete sentence or no useful source-language word or phrase example exists. It contains the source-language expression and one usage example for short lexical inputs. The ready `translation` is returned separately and is not repeated in this object.

## Request limits

- Registration and login: 10 requests per minute.
- Social exchange: 10 requests per minute.
- Password reset: 5 requests per minute.
- Translation creation: 60 requests per minute.
- Translation text: 1–12,000 characters.

Laravel validation, authentication, authorization, and rate-limit failures use the backend's status and error body. Authentication/authorization errors include stable `code` values where the storefront needs distinct handling. Treat non-2xx responses as failures and never expose provider credentials or metadata to clients.

## See Also

- [Authentication](authentication.md) — token flows and route protection
- [Translation Lifecycle](translation-lifecycle.md) — polling and cancellation
- [Configuration](configuration.md) — API base URLs
