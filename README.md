# NativeLens

> Translate, understand, and sound natural.

NativeLens is an AI-assisted translation and language-learning web application. It combines translation with grammar correction, error explanations, and a natural-sounding rewrite so learners can understand both the answer and the language behind it.

## Quick Start

Prerequisites: Docker Compose and a working Docker installation.

```bash
cp .docker/dev/env/.env.example .docker/dev/env/.env
cp httpdocs/backend/.env.example httpdocs/backend/.env
cp httpdocs/frontend/.env.example httpdocs/frontend/.env
docker compose -f .docker/dev/docker-compose.yml up -d --build
```

Initialize the backend:

```bash
docker compose -f .docker/dev/docker-compose.yml exec language-study-php-fpm composer install
docker compose -f .docker/dev/docker-compose.yml exec language-study-php-fpm php artisan key:generate
docker compose -f .docker/dev/docker-compose.yml exec language-study-php-fpm php artisan install:api --passport
docker compose -f .docker/dev/docker-compose.yml exec language-study-php-fpm php artisan migrate --seed
```

Open the storefront at [http://localhost:8080](http://localhost:8080).

## Key Features

- **Three-layer review** — See the source, translation, and language-coach feedback separately.
- **Cancellable translation requests** — Editing new text cancels the previous local and provider operation.
- **Provider abstraction** — Translation code depends on a provider contract, with OpenAI as the first adapter.
- **OAuth2 authentication** — Passport handles API tokens; Socialite supports Google, GitHub, and Facebook sign-in.
- **Permission-based administration** — Filament Shield and Spatie Permission control admin access.

## Example

After signing in, enter text in the translation workbench. NativeLens submits the text after a short debounce, polls the asynchronous request, and displays the translation, natural rewrite, and language issues independently.

## Documentation

| Guide | Description |
|-------|-------------|
| [Getting Started](docs/getting-started.md) | Local setup and first run |
| [Architecture](docs/architecture.md) | Applications, layers, and data flow |
| [Authentication](docs/authentication.md) | Storefront and admin authentication |
| [AI Providers](docs/ai-providers.md) | Provider contract and OpenAI adapter |
| [Translation Lifecycle](docs/translation-lifecycle.md) | Debounce, queues, polling, and cancellation |
| [Admin Authorization](docs/admin-authorization.md) | Roles, permissions, and panel access |
| [Configuration](docs/configuration.md) | Environment variables and runtime settings |
| [API Reference](docs/api.md) | HTTP endpoints and OpenAPI contract |
| [Deployment](docs/deployment.md) | Production topology and security |

The machine-readable API contract is available at [`httpdocs/backend/openapi/openapi.yaml`](httpdocs/backend/openapi/openapi.yaml) and OpenAPI docs for [`other pages`]([`httpdocs/backend/openapi/openapi.yaml`](httpdocs/backend/openapi/pages/)).

## Stack

- Backend: Laravel 13 and PHP 8.5
- Frontend: Nuxt 4, Vue 3, and TypeScript
- Authentication: Laravel Passport and Socialite
- Administration: Filament 5, Livewire 4, and Filament Shield
- Data and queues: MariaDB, Redis, and Laravel Horizon
- UI: FlyonUI with Tailwind CSS 4

## License

Proprietary.