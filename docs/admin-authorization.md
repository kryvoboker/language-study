[← Translation Lifecycle](translation-lifecycle.md) · [Back to README](../README.md) · [Configuration →](configuration.md)

# Admin Authorization

## Roles

Filament Shield and Spatie Permission own roles and permissions.

| Role | Admin access |
|------|--------------|
| `user` | Storefront only; never receives `access_admin_panel` |
| `admin` | May receive granular Shield permissions and `access_admin_panel` |
| `super_admin` | Bypasses permission checks through `Gate::before` |

## Panel access checks

`User::canAccessPanel()` requires all of the following:

- the account is not blocked
- the email is verified
- the user has `access_admin_panel`
- the user is not assigned the `user` role

Assigning `user` removes admin access immediately. The Filament User form exposes role selection for administrators.

## Operational guidance

- Grant the smallest set of Shield permissions needed for a role.
- Reserve `super_admin` for trusted operators.
- Add multi-factor authentication before production admin use.
- Review blocked and unverified accounts before granting access.

## See Also

- [Authentication](authentication.md) — storefront tokens and admin sessions
- [Deployment](deployment.md) — production security recommendations
- [Configuration](configuration.md) — application and service settings
