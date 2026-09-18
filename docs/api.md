[← Configuration](configuration.md) · [Back to README](../README.md) · [Deployment →](deployment.md)

# API Reference

The API is versioned under `/api/v1`. The complete OpenAPI 3.1 contract is [`openapi.yaml`](openapi.yaml), and Laravel renders it at `/docs/api`.

## Authentication

Protected routes require a Passport bearer token and verified email. Public authentication routes are rate-limited separately.

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
| `POST` | `/auth/email/verification-notification` | Passport + unverified | Resend verification mail |

## User and translation endpoints

Translation endpoints accept either a Passport bearer token or an authenticated Laravel web session (including the Filament admin session). Session-authenticated state-changing requests must include a valid CSRF token.

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `GET` | `/me` | Return the authenticated user |
| `POST` | `/translation-requests` | Create a translation request |
| `GET` | `/translation-requests/{translation_request}` | Read status or result |
| `DELETE` | `/translation-requests/{translation_request}` | Cancel a request |

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

Laravel validation, authentication, authorization, and rate-limit failures use the backend's status and error body. Treat non-2xx responses as failures and never expose provider credentials or metadata to clients.

## See Also

- [Authentication](authentication.md) — token flows and route protection
- [Translation Lifecycle](translation-lifecycle.md) — polling and cancellation
- [Configuration](configuration.md) — API base URLs
