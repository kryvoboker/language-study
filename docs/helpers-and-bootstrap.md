[← Architecture](architecture.md) · [Back to README](../README.md) · [Authentication →](authentication.md)

# Helpers and Application Bootstrap

The backend exposes small, reusable PHP helpers from `httpdocs/backend/app/Supports/helpers.php`. The file is registered in Composer's `autoload.files`, so the helpers are available throughout the Laravel application without importing a class.

## Value conversion helpers

Use these helpers at boundaries where input can have an unexpected type:

| Function | Behavior |
|----------|----------|
| `string_value(mixed $value): string` | Returns a scalar value as a string; otherwise returns an empty string. |
| `integer_value(mixed $value): int` | Casts numeric input to an integer; otherwise returns `0`. |
| `float_value(mixed $value): float` | Casts numeric input to a float; otherwise returns `0.0`. |
| `boolean_value(mixed $value): bool` | Parses a boolean value with `FILTER_VALIDATE_BOOL`; invalid or missing values become `false`. |
| `array_value(mixed $value): array` | Returns the value when it is an array; otherwise returns an empty array. |
| `list_value(mixed $value): list<mixed>` | Returns an array re-indexed as a list; non-arrays become an empty list. |
| `string_keyed_array(mixed $value): array<string, mixed>` | Keeps only entries whose keys are strings; non-arrays become an empty array. |

## String and URL helpers

| Function | Behavior |
|----------|----------|
| `nullable_string(mixed $value): ?string` | Converts input to a string, trims it with `Str::trim()`, and returns `null` for an empty result. |
| `resolve_string(mixed $value, string $fallback): string` | Returns `nullable_string($value)` or the supplied fallback. |
| `resolve_public_url(mixed $url): ?string` | Accepts only absolute `http://` or `https://` URLs and root-relative paths; all other values return `null`. |
| `decode_html_entities(?string $string): string` | Decodes HTML entities using UTF-8, quotes, and substitution for invalid sequences. |
| `escape_special_html(?string $html_string): string` | Decodes entities, escapes contents of `<script>` blocks, and replaces apostrophes with `&apos;`. |
| `sanitize_str(?string $string): string` | Removes HTML tags, decodes entities, trims whitespace, and collapses whitespace runs to one space. |
| `trim_strs_in_arr(array $arr): array` | Applies `Str::trim()` to string values while preserving non-string values and array keys. |

## JSON helpers

| Function | Behavior |
|----------|----------|
| `json_encode_throw(mixed $value, int $flags = 0, int $depth = 512): false|string` | Encodes JSON with `JSON_THROW_ON_ERROR` added to the supplied flags. |
| `json_decode_throw(string $json, ?bool $associative = true, int $depth = 512, int $flags = 0): mixed` | Decodes JSON with `JSON_THROW_ON_ERROR` added to the supplied flags. |

Use the JSON helpers when malformed JSON must become an exception instead of being silently converted to `false` or `null`.

## Telephone helpers

| Function | Behavior |
|----------|----------|
| `clear_telephone(?string $telephone, bool $is_delete_first_nums = false): string` | Removes non-digits. With the second argument enabled, it also removes the leading Ukrainian `38` prefix. |
| `parse_telephone(string $telephone): string` | Normalizes the telephone value and places its digits into the `+38 (___) ___-__-__` mask. |

These helpers are presentation-oriented; validate telephone input separately before persisting or using it for authentication.

## Bootstrap helper and application setup

`httpdocs/backend/bootstrap/app.php` defines `string_to_array(?string $string, string $separator = ','): array`. It converts a nullable, separator-delimited string into a trimmed, zero-based array and removes empty items. This is useful for environment values such as comma-separated origins or hosts.

The same bootstrap file configures the Laravel application:

- Registers `routes/web.php`, `routes/api.php`, `routes/console.php`, and the `/up` health endpoint.
- Trusts proxies from every address with `trustProxies(at: '*')`; review this setting before deploying behind an untrusted proxy boundary.
- Provides a placeholder for project-specific exception mappings.
- Requires the `NEW_STORAGE_PATH` environment variable and stops application startup when it is missing.
- Overrides Laravel's storage path with `NEW_STORAGE_PATH` immediately after application creation.

Environment access belongs in configuration or bootstrap wiring. Application code should consume configuration values through `config()` rather than calling `env()` directly.

## See Also

- [Architecture](architecture.md) — backend boundaries and storage isolation
- [Configuration](configuration.md) — environment variables and runtime settings
- [Getting Started](getting-started.md) — local setup and service startup
