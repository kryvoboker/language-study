[Back to README](../README.md) · [Architecture →](architecture.md)

# Getting Started

## Prerequisites

- Docker with Compose support
- Git, when working from a checkout
- Access to the external Docker networks `traefik-network` and `language-study-net` used by the provided Compose file

When run outside containers, the backend requires PHP 8.5 and the frontend requires Node.js 25.6.1 or newer.

## Configure and start

```bash
cp .docker/dev/env/.env.example .docker/dev/env/.env
cp httpdocs/backend/.env.example httpdocs/backend/.env
cp httpdocs/frontend/.env.example httpdocs/frontend/.env
docker compose -f .docker/dev/docker-compose.yml up -d --build
```

Review the copied values before starting. Set application keys, database credentials, mail settings, social credentials, and the provider credential when those integrations are enabled.

The Compose file starts PHP-FPM, Nginx, Node.js, and MariaDB. The storefront is available at `http://localhost:8080`; the Nuxt development server is mapped to port `3001`.

## Initialize Laravel

```bash
docker compose -f .docker/dev/docker-compose.yml exec language-study-php-fpm composer install
docker compose -f .docker/dev/docker-compose.yml exec language-study-php-fpm php artisan key:generate
docker compose -f .docker/dev/docker-compose.yml exec language-study-php-fpm php artisan install:api --passport
docker compose -f .docker/dev/docker-compose.yml exec language-study-php-fpm php artisan migrate --seed
docker compose -f .docker/dev/docker-compose.yml exec language-study-php-fpm php artisan passport:public-client
```

If Passport did not create keys and a personal client, run:

```bash
docker compose -f .docker/dev/docker-compose.yml exec language-study-php-fpm php artisan passport:keys
docker compose -f .docker/dev/docker-compose.yml exec language-study-php-fpm php artisan passport:client --personal
```

If needed, install frontend dependencies with:

```bash
docker compose -f .docker/dev/docker-compose.yml exec language-study-nodejs npm install
```

## Verify the installation

1. Open `http://localhost:8080`.
2. Copy the public client ID from `passport:public-client` into `NUXT_PUBLIC_PASSPORT_CLIENT_ID` and restart Nuxt.
3. Register a user and open the signed verification link from Mailpit.
4. Sign in; the BFF keeps Passport tokens in HttpOnly cookies.
5. Enter source text in the workbench and confirm the request reaches `completed`.
6. Visit `/docs/api` to inspect the rendered API contract.

## Create an administrator

After migrations, create a user and assign `super_admin`, or adapt `DatabaseSeeder` for local use. Never seed or reuse a production password.

## Quality checks

Run backend commands from `httpdocs/backend` and frontend commands from `httpdocs/frontend`:

```bash
composer pint:test
composer phpcs:check
composer phpstan:local:all
composer phpmd:production
composer psalm:local
php artisan test
npm run ts:check
npm run test
npm run build
```

## See Also

- [Configuration](configuration.md) — environment variables and defaults
- [Architecture](architecture.md) — application structure
- [Deployment](deployment.md) — production setup
