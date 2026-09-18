<?php

declare(strict_types=1);

use JetBrains\PhpStorm\Language;
use Illuminate\Support\Facades\Log;

if (!function_exists('string_value')) {
	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	function string_value(mixed $value): string
	{
		return is_scalar($value) ? (string)$value : '';
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
		return is_numeric($value) ? (int)$value : 0;
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
		if (!is_array($value)) {
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
		return is_numeric($value) ? (float)$value : 0.0;
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

if (!function_exists('to_json')) {
	/**
	 * @param mixed $value
	 * @param int   $flags
	 * @param int   $depth
	 *
	 * @return string
	 * @throws JsonException
	 */
	function to_json(
		mixed $value,
		int   $flags = 0,
		int   $depth = 512
	): string {
		if (blank($value)) {
			return '';
		}

		$json = json_encode($value, $flags | JSON_UNESCAPED_UNICODE, $depth);

		// if you pass the JSON_THROW_ON_ERROR, so current condition doesn't work
		if (json_last_error() !== JSON_ERROR_NONE) {
			Log::channel('stack')->error(
				"Failed encode to JSON!\nError: " . json_last_error_msg(),
				log_stack_trace(),
			);

			return '';
		}

		return $json;
	}
}

if (!function_exists('from_json')) {
	/**
	 * @param string    $json
	 * @param bool|null $associative
	 * @param int       $depth
	 * @param int       $flags
	 *
	 * @return array|object
	 * @throws JsonException
	 */
	function from_json(
		#[Language("JSON")] string $json,
		?bool                      $associative = true,
		int                        $depth = 512,
		int                        $flags = 0
	): array|object {
		if ($json === '') {
			return [];
		}

		$data = json_decode($json, $associative, $depth, $flags);

		// if you pass the JSON_THROW_ON_ERROR, so current condition doesn't work
		if (json_last_error() !== JSON_ERROR_NONE) {
			Log::channel('stack')->error(
				"Failed decode JSON!\nError: " . json_last_error_msg(),
				log_stack_trace(),
			);

			return [];
		}

		return $data;
	}
}

if (!function_exists('json_encode_throw')) {
	/**
	 * @param mixed $value
	 * @param int   $flags
	 * @param int   $depth
	 *
	 * @return string
	 * @throws JsonException
	 */
	function json_encode_throw(
		mixed $value,
		int   $flags = 0,
		int   $depth = 512
	): string {
		return to_json($value, $flags | JSON_THROW_ON_ERROR, $depth);
	}
}

if (!function_exists('json_decode_throw')) {
	/**
	 * @param string    $json
	 * @param bool|null $associative
	 * @param int       $depth
	 * @param int       $flags
	 *
	 * @return array|object
	 * @throws JsonException
	 */
	function json_decode_throw(
		#[Language("JSON")] string $json,
		?bool                      $associative = true,
		int                        $depth = 512,
		int                        $flags = 0
	): array|object {
		return from_json($json, $associative, $depth, $flags | JSON_THROW_ON_ERROR);
	}
}

if (!function_exists('decode_html_entities')) {
	/**
	 * @param string|null $string
	 *
	 * @return string
	 */
	function decode_html_entities(?string $string): string
	{
		return html_entity_decode((string)$string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}

if (!function_exists('escape_special_html')) {
	/**
	 * @param string|null $html_string
	 *
	 * @return string
	 */
	function escape_special_html(?string $html_string): string
	{
		$prepared_html = preg_replace_callback('/<script>.*?<\/script>/s', function (array $match): string {
			return Str::replace(['<', '>'], ['&lt;', '&gt;'], $match[0], false);
		}, decode_html_entities($html_string)) ?? '';

		return Str::replace("'", '&apos;', $prepared_html, false);
	}
}

if (!function_exists('sanitize_str')) {
	/**
	 * @param string|null $string
	 *
	 * @return string
	 */
	function sanitize_str(?string $string): string
	{
		if ($string === null) {
			return '';
		}

		$sanitized_value = Str::trim(strip_tags(decode_html_entities($string)));

		return (string)Str::replaceMatches('/\s+/', ' ', $sanitized_value);
	}
}

if (!function_exists('clear_telephone')) {
	/**
	 * @param string|null $telephone
	 * @param bool        $is_delete_first_nums
	 *
	 * @return string
	 */
	function clear_telephone(?string $telephone, bool $is_delete_first_nums = false): string
	{
		if (!isset($telephone)) {
			return '';
		}

		if ($is_delete_first_nums) {
			return preg_replace(['/\D+/', '/^38/'], '', $telephone) ?: $telephone;
		}

		return preg_replace('/\D+/', '', $telephone) ?: $telephone;
	}
}

if (!function_exists('parse_telephone')) {
	/**
	 * @param string $telephone
	 *
	 * @return string
	 */
	function parse_telephone(string $telephone): string
	{
		$telephone = clear_telephone($telephone, true);

		$mask         = '+38 (___) ___-__-__';
		$phone_length = Str::length($telephone);

		for ($index_number = 0; $index_number < $phone_length; $index_number++) {
			$mask = (string)Str::replaceMatches('/_/', $telephone[$index_number], $mask, 1);
		}

		return $mask;
	}
}

if (!function_exists('trim_strs_in_arr')) {
	/**
	 * @param array<int|string, mixed> $arr
	 *
	 * @return array<int|string, mixed>
	 */
	function trim_strs_in_arr(array $arr): array
	{
		return array_map(function ($item) {
			if (is_string($item)) {
				return Str::trim($item);
			}

			return $item;
		}, $arr);
	}
}

if (!function_exists('log_stack_trace')) {
	/**
	 * @return array
	 */
	function log_stack_trace(): array
	{
		$stack_trace = debug_backtrace();
		$log_message = [];

		foreach ($stack_trace as $stack_frame) {
			$file     = $stack_frame['file'] ?? '(no file)';
			$line     = $stack_frame['line'] ?? '(no line)';
			$function = $stack_frame['function'] ?? '(no function)';
			$type     = $stack_frame['type'] ?? ' - ';

			$log_message[] = sprintf("#%d %s:%s $type %s", $line, $file, $line, $function);
		}

		return $log_message;
	}
}