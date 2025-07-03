<?php

/**
 * Class Arr
 * A collection of null-aware array utility functions.
 */
class Arr {
	/**
	 * Recursively escapes an array of strings for use in HTML.
	 *
	 * @param array $array The array to escape.
	 * @return array The escaped array.
	 */
	public static function safe(array $array): array {
		$escapedArray = [];

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$escapedArray[$key] = self::safe($value);
			} elseif (is_string($value)) {
				$escapedArray[$key] = Str::safe($value);
			} else {
				$escapedArray[$key] = $value;
			}
		}

		return $escapedArray;
	}

	/**
	 * Get the value from an array using a key, with null safety.
	 * Or provided default if array is null, or key was not found
	 *
	 * @param null|array $array
	 * @param string|int $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get(null|array $array, string|int $key, mixed $default = null): mixed {
		if ($array === null || !array_key_exists($key, $array)) return $default;
		return $array[$key];
	}


	/**
	 * Checks if a given value exists in the provided array.
	 *
	 * @param array|null $array The array to search in. If null, the function returns false.
	 * @param string|int $value The value to search for within the array.
	 * @return bool Returns true if the value is found in the array, false otherwise.
	 */
	public static function contains(null|array $array, string|int $value): bool {
		if ($array === null) return false;
		return in_array($value, $array, true);
	}

	/**
	 * Check if a key exists in an array, with null safety.
	 *
	 * @param null|array $array
	 * @param string|int $key
	 * @return bool
	 */
	public static function has(null|array $array, string|int $key): bool {
		if ($array === null) return false;
		return array_key_exists($key, $array);
	}

	/**
	 * Set a value in an array by key, with null safety.
	 *
	 * @param null|array $array
	 * @param string|int $key
	 * @param mixed $value
	 * @return array
	 */
	public static function set(null|array &$array, string|int $key, mixed $value): array {
		if ($array === null) $array = [];
		$array[$key] = $value;
		return $array;
	}

	/**
	 * Remove a key from an array, with null safety.
	 *
	 * @param null|array $array
	 * @param string|int $key
	 * @return array
	 */
	public static function forget(null|array &$array, string|int $key): array {
		if ($array === null) return [];
		unset($array[$key]);
		return $array;
	}

	/**
	 * Check if an array is empty or null.
	 *
	 * @param null|array $array
	 * @return bool
	 */
	public static function isEmpty(null|array $array): bool {
		return empty($array);
	}

	/**
	 * Flatten a multi-dimensional array into a single level, with null safety.
	 *
	 * @param null|array $array
	 * @return array
	 */
	public static function flatten(null|array $array): array {
		if ($array === null) return [];
		$result = [];
		array_walk_recursive($array, static function ($value) use (&$result) {
			$result[] = $value;
		});
		return $result;
	}

	/**
	 * Get a slice of the array, with null safety.
	 *
	 * @param null|array $array
	 * @param int $offset
	 * @param null|int $length
	 * @return array
	 */
	public static function slice(null|array $array, int $offset, null|int $length = null): array {
		if ($array === null) return [];
		return array_slice($array, $offset, $length);
	}

	/**
	 * Merge multiple arrays together, with null safety.
	 *
	 * @param null|array ...$arrays
	 * @return array
	 */
	public static function merge(null|array ...$arrays): array {
		return array_merge(...array_map(static fn($array) => $array ?? [], $arrays));
	}

	/**
	 * Filter elements of an array using a callback, with null safety.
	 *
	 * @param null|array $array
	 * @param null|callable $callback
	 * @param int $mode
	 * @return array
	 */
	public static function filter(null|array $array, ?callable $callback = null, int $mode = 0): array {
		if ($array === null) return [];
		return array_filter($array, $callback, $mode);
	}

	/**
	 * Map over each element in the array, with null safety.
	 *
	 * @param null|array $array
	 * @param callable $callback
	 * @return array
	 */
	public static function map(null|array $array, callable $callback): array {
		if ($array === null) return [];
		return array_map($callback, $array);
	}

	/**
	 * Join array elements with a string, with null safety.
	 *
	 * @param null|array $array
	 * @param string $glue
	 * @return string
	 */
	public static function join(null|array $array, string $glue): string {
		if ($array === null) return '';
		return implode($glue, $array);
	}
}
