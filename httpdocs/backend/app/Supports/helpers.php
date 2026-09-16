<?php

declare(strict_types=1);

if (!function_exists('string_value')) {
    /**
     * @param mixed $value
     *
     * @return string
     */
    function string_value(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}

if (!function_exists('integer_value')) {
    /**
     * @param mixed $value
     *
     * @return int
     */
    function integer_value(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}

if (!function_exists('array_value')) {
    /**
     * @param mixed $value
     *
     * @return array<int|string, mixed>
     */
    function array_value(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }
}

if (!function_exists('string_keyed_array')) {
    /**
     * @param mixed $value
     *
     * @return array<string, mixed>
     */
    function string_keyed_array(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_filter($value, function ($key) {
            return is_string($key);
        }, ARRAY_FILTER_USE_KEY);
    }
}

if (!function_exists('boolean_value')) {
    /**
     * @param mixed $value
     *
     * @return bool
     */
    function boolean_value(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
    }
}

if (!function_exists('float_value')) {
    /**
     * @param mixed $value
     *
     * @return float
     */
    function float_value(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}

if (!function_exists('list_value')) {
    /**
     * @param mixed $value
     *
     * @return list<mixed>
     */
    function list_value(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [];
    }
}

if (!function_exists('nullable_string')) {
    /**
     * @param mixed $value
     *
     * @return string|null
     */
    function nullable_string(mixed $value): ?string
    {
        $value = Str::trim(string_value($value));

        return $value !== '' ? $value : null;
    }
}

if (!function_exists('resolve_string')) {
    /**
     * @param mixed  $value
     * @param string $fallback
     *
     * @return string
     */
    function resolve_string(mixed $value, string $fallback): string
    {
        return nullable_string($value) ?? $fallback;
    }
}

if (!function_exists('resolve_public_url')) {
    /**
     * @param mixed $url
     *
     * @return string|null
     */
    function resolve_public_url(mixed $url): ?string
    {
        $url = nullable_string($url);

        return $url !== null && Str::startsWith($url, ['https://', 'http://', '/']) ? $url : null;
    }
}
