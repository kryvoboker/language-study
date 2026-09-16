[← Getting Started](getting-started.md) · [Back to README](../README.md) · [Authentication →](authentication.md)

# Architecture

## Overview

NativeLens contains two deployable applications under `httpdocs/`:

- `backend` — Laravel domain, API, admin panel, queues, and persistence.
- `frontend` — Nuxt SSR storefront and translation workbench.

Development Nginx exposes one origin. Laravel owns `/api`, `/oauth`, `/admin`, `/docs`, and authentication callbacks; other storefront traffic is handled by Nuxt.

## Repository structure

```text
.
├── .docker/dev/                 # Development images, services, and environment files
├── docs/                        # Human-readable and machine-readable documentation
├── httpdocs/backend/            # Laravel application
│   ├── app/Domain/Ai/            # Provider contracts, DTOs, and adapters
│   ├── app/Domain/Translation/   # Translation actions and data objects
│   ├── app/Jobs/                 # Asynchronous translation work
│   ├── routes/                   # API and web routes
│   └── database/                 # Migrations and seeders
├── httpdocs/frontend/           # Nuxt application
│   └── app/                      # Pages, components, composables, and plugins
└── storage/                     # External Laravel storage mount
```

## Backend boundaries

| Area | Responsibility |
|------|----------------|
| `Domain/Ai` | Provider-neutral contracts, DTOs, selection, and adapters |
| `Domain/Translation` | Translation-specific actions and prompt data |
| `Jobs` | Start and poll asynchronous provider operations |
| `Http/Controllers/Api/V1` | HTTP transport and request/response orchestration |
| `Filament` | Administration UI and resource authorization |

Controllers remain transport-focused. Provider-specific SDK behavior belongs in an adapter implementing `AiProviderContract`.

## Translation data flow

1. Nuxt debounces input for 650 ms.
2. Laravel validates, rate-limits, and persists a `translation_requests` row.
3. `StartTranslationJob` starts a cancellable background provider operation.
4. The operation ID is stored, and `PollTranslationJob` retrieves it until terminal state.
5. The API returns translation data, the natural rewrite, and language issues.
6. Editing the source cancels the active local request and propagates cancellation upstream.

This keeps PHP-FPM requests short-lived, allows independent queue scaling, and avoids coupling the domain model to one AI vendor.

## Storage and isolation

Laravel storage is mounted outside `httpdocs/backend` and configured in `bootstrap/app.php` and `AppServiceProvider`. `NEW_STORAGE_PATH` controls the location.

Storefront resources intentionally exclude provider names, models, API keys, and provider IDs. Those values remain server-side for observability and billing.

## See Also

- [Translation Lifecycle](translation-lifecycle.md) — asynchronous request behavior
- [AI Providers](ai-providers.md) — provider abstraction details
- [Deployment](deployment.md) — runtime topology and scaling