[← API Reference](api.md) · [Back to README](../README.md) · [Filament Resources →](filament-resources.md)

# Deployment

## Minimum topology

- Nginx or another edge proxy
- Nuxt SSR nodes
- Laravel PHP-FPM API and admin nodes
- Horizon workers dedicated to the `translations` queue
- a scheduler node
- Redis
- MariaDB

The development Docker Compose file provides PHP-FPM, Nginx, Node.js, and MariaDB. Production environments may split these responsibilities across managed services and independently scaled nodes.

## Build and release outline

1. Build backend and frontend images from the repository.
2. Provide environment-specific backend and frontend variables.
3. Run `composer install --no-dev --optimize-autoloader` in the backend image.
4. Build Nuxt with `npm run build`.
5. Run migrations as a controlled release step.
6. Start PHP-FPM, Nuxt, Horizon, and the scheduler.
7. Verify `/docs/api`, authentication, and a translation request through the edge proxy.

## Scale-out

API and frontend nodes are stateless. Translation work is queue-backed, so Horizon worker count can be increased independently according to queue depth and provider rate limits. Redis Sentinel or Cluster may be appropriate for larger deployments.

## Security checklist

- Keep Passport private keys outside the repository.
- Encrypt AI provider secrets.
- Use TLS everywhere.
- Use secure, HTTP-only cookies for server-held auth state.
- Rate-limit translation and authentication endpoints separately.
- Protect the admin panel with MFA in production.
- Rotate social, OAuth, and provider secrets.
- Forward only trusted proxy headers.
- Use managed MariaDB backups and centralized logs.

## See Also

- [Configuration](configuration.md) — environment variables
- [Authentication](authentication.md) — token and session boundaries
- [Admin Authorization](admin-authorization.md) — production access controls
