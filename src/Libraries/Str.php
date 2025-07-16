<?php

/**
 * The Str class provides string manipulation methods.
 * This class handles null values gracefully, ensuring that methods return a value of a predictable type
 * when one or one or more of the input strings are null, preventing potential errors
 * and allowing for more robust string manipulation operations.
 */
class Str {
	/**
	 * Null aware string escape
	 *
	 * @param null|string $string The string to escape.
	 * @return string The escaped string.
	 */
	public static function safe(null|string $string) {
		if ($string === null) return '';
		return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
	}

	/**
	 * Null aware base64 encoding
	 * 
	 * @param null|string $string The string to encode to base64.
	 * @return string The encoded version of $string, empty if $string was null
	 */
	public static function encode(null|string $string): string {
		if ($string === null) return '';
		return base64_encode($string);
	}

	/**
	 * Null aware base64 decoding.
	 * 
	 * @param null|string $string The string to decode from base64.
	 * @return string The decoded version of $string, empty if $string was null
	 */
	public static function decode(null|string $string): string {
		if ($string === null) return '';
		return base64_decode($string);
	}

	/**
	 * Null aware multibyte string length count
	 * 
	 * @param null|string $string The string to measure.
	 * @return int The length of $string, 0 if $string was null
	 */
	public static function len(null|string $string): int {
		if ($string === null) return 0;
		return mb_strlen($string);
	}

	/**
	 * Null aware multibyte lowercase convertion.
	 * 
	 * @param null|string $string The string to uppercase.
	 * @return string The lowercased string, empty if $string was null
	 */
	public static function lower(null|string $string): string {
		if ($string === null) return '';
		return mb_strtolower($string);
	}

	/**
	 * Null aware multibyte uppercase convertion.
	 * @param null|string $string The string to uppercase.
	 * @return string The uppercased string, empty if $string was null
	 */
	public static function upper(null|string $string): string {
		if ($string === null) return '';
		return mb_strtoupper($string);
	}

	/**
	 * Null aware substring extraction.
	 *
	 * @param null|string $string The string to extract from.
	 * @param int         $start  The start position.
	 * @param int|null    $length (optional) The length of the substring.
	 * @return string The extracted substring, empty if $string was null.
	 */
	public static function substr(?string $string, int $start, ?int $length = null): string {
		if ($string === null) return '';
		return mb_substr($string, $start, $length);
	}

	/**
	 * Capitalize the first letter of a string (multibyte safe).
	 *
	 * @param null|string $string The string to capitalize.
	 * @return string The capitalized string, empty if $string was null.
	 */
	public static function ucfirst(?string $string): string {
		if ($string === null || $string === '') return '';
		return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
	}

	/**
	 * Check if a string contains another string (case-sensitive).
	 *
	 * @param string|null $haystack The string to search in.
	 * @param string|null $needle The string to search for.
	 * @return bool
	 */
	public static function contains(?string $haystack, ?string $needle): bool {
		if ($haystack === null || $needle === null) return false;
		return str_contains($haystack, $needle);
	}

	/**
	 * Check if a string contains another string (case-insensitive).
	 *
	 * @param string|null $haystack The string to search in.
	 * @param string|null $needle The string to search for.
	 * @return bool
	 */
	public static function containsIgnoreCase(?string $haystack, ?string $needle): bool {
		if ($haystack === null || $needle === null) return false;
		return mb_stripos($haystack, $needle) !== false;
	}

	/**
	 * Check if a string starts with another string (case-sensitive).
	 *
	 * @param string|null $haystack The string to check.
	 * @param string|null $needle The prefix to look for.
	 * @return bool
	 */
	public static function startsWith(?string $haystack, ?string $needle): bool {
		if ($haystack === null || $needle === null) return false;
		return str_starts_with($haystack, $needle);
	}

	/**
	 * Check if a string starts with another string (case-insensitive).
	 *
	 * @param string|null $haystack The string to check.
	 * @param string|null $needle The prefix to look for.
	 * @return bool
	 */
	public static function startsWithIgnoreCase(?string $haystack, ?string $needle): bool {
		if ($haystack === null || $needle === null) return false;
		return mb_stripos($haystack, $needle) === 0;
	}

	/**
	 * Check if a string ends with another string (case-sensitive).
	 *
	 * @param string|null $haystack The string to check.
	 * @param string|null $needle The suffix to look for.
	 * @return bool
	 */
	public static function endsWith(?string $haystack, ?string $needle): bool {
		if ($haystack === null || $needle === null) return false;
		return str_ends_with($haystack, $needle);
	}

	/**
	 * Check if a string ends with another string (case-insensitive).
	 *
	 * @param string|null $haystack The string to check.
	 * @param string|null $needle The suffix to look for.
	 * @return bool
	 */
	public static function endsWithIgnoreCase(?string $haystack, ?string $needle): bool {
		if ($haystack === null || $needle === null) return false;

		$needleLength = mb_strlen($needle);

		return $needleLength === 0 || substr_compare(
			mb_strtolower($haystack),
			mb_strtolower($needle),
			-$needleLength,
			$needleLength
		) === 0;
	}

	/**
	 * Check if a string matches a given pattern (case-sensitive).
	 *
	 * @param string|null $pattern The pattern to match.
	 * @param string|null $subject The string to search in.
	 * @return bool
	 */
	public static function test(?string $pattern, ?string $subject): bool {
		if ($subject === null || $pattern === null) return false;
		return preg_match($pattern, $subject) === 1;
	}

	/**
	 * Check if a string matches a given pattern (case-sensitive).
	 *
	 * @param string|null  $pattern The pattern to match.
	 * @param string|null $subject The string to search in.
	 * @param int         $flags   (optional) A bitmask of flags (default is 0).
	 * @param int         $offset  (optional) The offset to start searching from (default is 0).
	 *
	 * @return string[] Return an array of matches, empty on failures or if any of $pattern or $subject is null.
	 */
	public static function match(?string $pattern, ?string $subject, int $flags = 0, int $offset = 0): array {
		if ($subject === null || $pattern === null) return [];
		$result = preg_match($pattern, $subject, $matches, $flags, $offset);
		return $result !== false ? $matches : [];
	}

	/**
	 * Match all occurrences of a pattern in a string.
	 *
	 * @param string|null $pattern The pattern to match.
	 * @param string|null $subject The string to search in.
	 * @param int         $flags   (optional) A bitmask of flags (default is 0).
	 * @param int         $offset  (optional) The offset to start searching from (default is 0).
	 *
	 * @return string[] Array of strings that match the pattern.
	 */
	public static function matchAll(?string $pattern, ?string $subject, int $flags = 0, int $offset = 0): array {
		if ($subject === null || $pattern === null) return [];
		$result = preg_match_all($pattern, $subject, $matches, $flags, $offset);
		return $result !== false ? ($matches[1] ?? $matches[0]) : [];
	}

	/**
	 * Normalize multiple spaces and other whitespace to a single space.
	 *
	 * @param null|string $string The string to normalize.
	 * @return string The normalized string, empty if $string was null.
	 */
	public static function normalizeWhitespace(?string $string): string {
		if ($string === null) return '';
		return preg_replace('/\s+/', ' ', $string);
	}

	/**
	 * Attempt to replace Unicode characters with their ASCII counterparts using dynamic character encoding detection.
	 *
	 * @param string $input The input string with Unicode characters.
	 * @return string The string with Unicode characters replaced by their ASCII counterparts. If transilteration fails, the original string is returned.
	 */
	public static function ascii(string $input): string {
		$encoding = mb_detect_encoding($input, mb_list_encodings());
		$output = iconv($encoding, 'ASCII//TRANSLIT//IGNORE', $input);

		return $output !== false ? $output : $input;
	}

	/**
	 * Converts a string into a slug-friendly format.
	 *
	 * @param null|string $string The string to slugify.
	 * @param string      $separator (optional) The separator to use in the slug.
	 * @return string The slugified string, empty if $string was null.
	 */
	public static function slug(?string $string, string $separator = '-'): string {
		if ($string === null) return '';
		$string = static::ascii($string);
		$string = static::lower($string);
		$string = preg_replace('/[^a-zA-Z0-9]+/', $separator, $string);
		return static::trim($string, $separator);
	}

	/**
	 * Null aware trimming.
	 *
	 * @param null|string $string The string to trim.
	 * @param string      $characters (optional) Characters to trim.
	 * @return string The trimmed string, empty if $string was null.
	 */
	public static function trim(?string $string, string $characters = " \t\n\r\0\x0B"): string {
		if ($string === null) return '';
		return trim($string, $characters);
	}

	/**
	 * Null aware right-trimming.
	 *
	 * @param null|string $string The string to trim.
	 * @param string      $characters (optional) Characters to trim.
	 * @return string The trimmed string, empty if $string was null.
	 */
	public static function rtrim(?string $string, string $characters = " \t\n\r\0\x0B"): string {
		if ($string === null) return '';
		return rtrim($string, $characters);
	}

	/**
	 * Null aware left-trimming.
	 *
	 * @param null|string $string The string to trim.
	 * @param string      $characters (optional) Characters to trim.
	 * @return string The trimmed string, empty if $string was null.
	 */
	public static function ltrim(?string $string, string $characters = " \t\n\r\0\x0B"): string {
		if ($string === null) return '';
		return ltrim($string, $characters);
	}

	/**
	 * Null aware string reversal.
	 *
	 * @param null|string $string The string to reverse.
	 * @param string|null $encoding The character encoding. If it is omitted, the internal character encoding value will be used.
	 * @return string The reversed string, empty if $string was null.
	 */
	public static function reverse(?string $string, null|string $encoding = null): string {
		if ($string === null) return '';
		$chars = mb_str_split($string, 1, $encoding ?: mb_internal_encoding());
		return implode('', array_reverse($chars));
	}

	/**
	 * Null aware string repetition.
	 *
	 * @param null|string $string The string to repeat.
	 * @param int         $times  Number of times to repeat.
	 * @return string The repeated string, empty if $string was null.
	 */
	public static function repeat(?string $string, int $times): string {
		if ($string === null) return '';
		return str_repeat($string, $times);
	}

	/**
	 * Null aware string explode.
	 *
	 * @param string $separator The separator to use for splitting the string.
	 * @param null|string $string The string to split.
	 * @param int $limit The maximum number of elements in the resulting array. If omitted, there is no limit.
	 * @return string[]
	 */
	public static function cut(string $separator, null|string $string, int $limit = PHP_INT_MAX): array {
		if ($string === null) return [];
		return explode($separator, $string, $limit);
	}

	/**
	 * Null aware string split (multibyte safe).
	 *
	 * @param null|string $string The string to split.
	 * @param int $length Length of each chunk.
	 * @param string|null $encoding Character encoding. If omitted, internal encoding is used.
	 * @return array The array of string chunks, empty if $string was null.
	 */
	public static function split(null|string $string, int $length = 1, null|string $encoding = null): array {
		if ($string === null) return [];
		return mb_str_split($string, $length, $encoding ?: mb_internal_encoding());
	}

	/**
	 * Null aware string replacement.
	 *
	 * @param null|string $search  The string to search for.
	 * @param null|string $replace The replacement string.
	 * @param null|string $subject The string to perform replacement on.
	 * @return string The resulting string after replacement, empty if $subject was null.
	 */
	public static function replace(?string $search, ?string $replace, ?string $subject): string {
		if ($subject === null) return '';
		return str_replace($search ?? '', $replace ?? '', $subject);
	}

	/**
	 * Null aware string join (implode).
	 *
	 * @param string $glue The string to use as glue.
	 * @param null|array $pieces The array of strings to join.
	 * @return string The joined string, empty if $pieces was null.
	 */
	public static function join(string $glue, ?array $pieces): string {
		if ($pieces === null) return '';
		return implode($glue, $pieces);
	}
}
