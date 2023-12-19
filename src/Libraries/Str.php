<?php
/**
 * The Str class provides string manipulation methods.
 * This class handles null values gracefully, ensuring that methods return a value of a predictable type
 * when one or one or more of the input strings are null, preventing potential errors
 * and allowing for more robust string manipulation operations.
 */
class Str {
	/**
	 * Null aware base64 encoding
	 * 
	 * @param null|string $string The string to encode to base64.
	 * @return string The encoded version of $string, empty if $string was null
	 */
	public static function encode(null|string $string): string {
		if($string === null) return '';
		return base64_encode($string);
	}

	/**
	 * Null aware base64 decoding.
	 * 
	 * @param null|string $string The string to decode from base64.
	 * @return string The decoded version of $string, empty if $string was null
	 */
	public static function decode(null|string $string): string {
		if($string === null) return '';
		return base64_decode($string);
	}

	/**
	 * Null aware multibyte string length count
	 * 
	 * @param null|string $string The string to measure.
	 * @return int The length of $string, 0 if $string was null
	 */
	public static function len(null|string $string): int {
		if($string === null) return 0;
		return mb_strlen($string);
	}

	/**
	 * Null aware multibyte lowercase convertion.
	 * 
	 * @param null|string $string The string to uppercase.
	 * @return string The lowercased string, empty if $string was null
	 */
	public static function lower(null|string $string): string {
		if($string === null) return '';
		return mb_strtolower($string);
	}

	/**
	 * Null aware multibyte uppercase convertion.
	 * @param null|string $string The string to uppercase.
	 * @return string The uppercased string, empty if $string was null
	 */
	public static function upper(null|string $string): string {
		if($string === null) return '';
		return mb_strtoupper($string);
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
}
