<?php

/**
 * Class Path
 *
 * Utility class for common path operations with null safety.
 */
class Path {
	/**
	 * Normalize a file path by removing redundant slashes and resolving relative segments like '.' and '..'.
	 *
	 * @param null|string $path The path to normalize.
	 * @return null|string The normalized path or null if the input is null.
	 */
	public static function normalize(null|string $path): null|string {
		if ($path === null) return null;

		$path = preg_replace('#/+#', '/', str_replace('\\', '/', $path));

		// Attempt to use realpath to resolve the path
		// Note: realpath fails in the following scenarios:
		// - The path does not exist on the filesystem
		// - The path includes symbolic links that cannot be resolved
		// - The current user does not have permissions to access components of the path
		// If realpath fails, resolve relative segments manually
		if (!realpath($path)) {
			$segments = explode('/', $path);
			$resolved = [];

			foreach ($segments as $segment) {
				if ($segment === '.' || $segment === '') continue;
				if ($segment === '..') {
					array_pop($resolved);
				} else {
					$resolved[] = $segment;
				}
			}

			return '/' . implode('/', $resolved);
		}

		return realpath($path);
	}

	/**
	 * Join multiple path segments into a single path.
	 *
	 * @param null|string ...$segments Path segments to join.
	 * @return null|string The combined path or null if any input is null.
	 */
	public static function join(null|string ...$segments): null|string {
		if (in_array(null, $segments, true)) return null;
		return rtrim(self::normalize(implode('/', $segments)), '/');
	}

	/**
	 * Get the base name of a path (file or directory name).
	 *
	 * @param null|string $path The path to extract the base name from.
	 * @return null|string The base name of the path or null if the input is null.
	 */
	public static function basename(null|string $path): null|string {
		if ($path === null) return null;
		return basename($path);
	}

	/**
	 * Get the file extension from the path.
	 *
	 * @param null|string $path The file path.
	 * @return null|string The file extension or null if the input is null.
	 */
	public static function extension(null|string $path): null|string {
		if ($path === null) return null;
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * Get the directory name of a path.
	 *
	 * @param null|string $path The path to extract the directory from.
	 * @return null|string The directory name or null if the input is null.
	 */
	public static function dirname(null|string $path): null|string {
		if ($path === null) return null;
		return dirname($path);
	}

	/**
	 * Check if the given path is absolute.
	 *
	 * @param null|string $path The path to check.
	 * @return null|bool True if the path is absolute, false otherwise, or null if the input is null.
	 */
	public static function isAbsolute(null|string $path): null|bool {
		if ($path === null) return null;
		return (bool) preg_match('#^([a-zA-Z]:|/)#', $path);
	}

	/**
	 * Resolve a relative path to an absolute path based on a base path.
	 *
	 * @param null|string $basePath The base path to resolve from.
	 * @param null|string $relativePath The relative path to resolve.
	 * @return null|string The resolved absolute path or null if any input is null.
	 */
	public static function resolve(null|string $basePath, null|string $relativePath): null|string {
		if ($basePath === null || $relativePath === null) return null;
		return self::join($basePath, $relativePath);
	}

	/**
	 * Convert a path to a URL-friendly format (slashes become forward slashes).
	 *
	 * @param null|string $path The path to convert.
	 * @return null|string The URL-friendly path or null if the input is null.
	 */
	public static function toUri(null|string $path): null|string {
		if ($path === null) return null;
		return str_replace('\\', '/', $path);
	}
}
