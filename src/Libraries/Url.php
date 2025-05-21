<?php

/**
 * This class provides utility methods for working with URLs,
 * such as parsing and manipulating URL components.
 *
 * It allows developers to extract specific parts of a URL,
 * build query strings, and perform URL encoding and decoding.
 *
 * The class aims to simplify common URL-related tasks and
 * enhance the handling of URLs within the PHP applications.
 */
class Url {
	/**
	 * Get the base URL of the current application.
	 *
	 * @return string The base URL of the application.
	 */
	public static function getBaseurl(): string {
		$baseurl = \Registry::getConfiguration()->get("baseurl");
		return $baseurl;
	}

	/**
	 * Get the provided uri appended to the baseurl of the application.
	 * @param string $uri Path to element of which to create a URI.
	 * @return string
	 */
	public static function fromUri(string $uri = ""): string {
		if (\Str::startsWith($uri, "http")) {
			return $uri;
		}

		$baseurl = self::getBaseurl();
		return $baseurl . '/' . ltrim($uri, "/");
	}

	/**
	 * Perform a redirect with a X-Redirect-By header, the application will be shutdown after the redirect.
	 * @param string $location Location of the redirect
	 * @param string $xRedirectBy Human readable indidcator of who performed this redirect
	 * @return void
	 */
	public static function redirect(string $location, string $xRedirectBy): never {
		header("HTTP/1.1 302 Found");
		header("Cache-Control: no-cache, must-revalidate");
		header("X-Redirect-By: " . $xRedirectBy);
		header("Location: " . $location);
		exit;
	}

	/**
	 * Parses a URL and returns an associative array containing its components.
	 * @param string $url The URL to parse.
	 * @link https://www.php.net/parse_url
	 * @return array|string|int|null|false Same as parse_url
	 */
	public static function parse(string $url): array|string|int|null|false {
		return parse_url($url);
	}

	/**
	 * Builds a URL from its components and returns the assembled string.
	 * 
	 * ```php
	 * <?php
	 * $urlParts = [
	 *     "scheme" => "https",
	 *     "host" => "example.com",
	 *     "path" => "/page",
	 *     "query" => "param=value",
	 *     "fragment" => "section",
	 * ];
	 *
	 * $url = \Url::build($urlParts);
	 * echo $url; // Outputs: "https://example.com/page?param=value#section"
	 * ```
	 * 
	 * @param array $urlParts An associative array of URL components.
	 *	                      Possible keys: scheme, host, user, pass, port, path, query, fragment.
	 * @return string|false The assembled URL string, or false on failure.
	 * 
	 */
	public static function build(array $urlParts): mixed {
		if (!isset($urlParts["scheme"]) || !isset($urlParts["host"])) {
			return false;
		}

		$url = $urlParts["scheme"] . "://";

		if (isset($urlParts["user"])) {
			$url .= $urlParts["user"];

			if (isset($urlParts["pass"])) {
				$url .= ":" . $urlParts["pass"];
			}

			$url .= "@";
		}

		$url .= $urlParts["host"];

		if (isset($urlParts["port"])) {
			$url .= ":" . $urlParts["port"];
		}

		if (isset($urlParts["path"])) {
			$url .= $urlParts["path"];
		}

		if (isset($urlParts["query"])) {
			$url .= "?" . $urlParts["query"];
		}

		if (isset($urlParts["fragment"])) {
			$url .= "#" . $urlParts["fragment"];
		}

		return $url;
	}

	/**
	 * Encodes a string by replacing special characters with their percent-encoded representation.
	 * @param string $string The string to encode.
	 * @return string The encoded string.
	 */
	public static function encode(string $string): string {
		return urlencode($string);
	}

	/**
	 * Decodes a percent-encoded string back to its original form.
	 * @param string $string The string to decode.
	 * @return string The decoded string.
	 */
	public static function decode(string $string): string {
		return urldecode($string);
	}

	/**
	 * Builds a query string from an associative array of parameters.
	 * @param array $params An associative array of URL parameters.
	 * @return string The built query string.
	 */
	public static function buildQueryString(array $params): string {
		return http_build_query($params);
	}

	/**
	 * Parses a query string and returns an associative array of its parameters.
	 * @param string $queryString The query string to parse.
	 * @return array|false An associative array of query parameters, or false on failure.
	 */
	public static function parseQueryString(string $queryString): mixed {
		parse_str($queryString, $params);
		return $params;
	}
}
