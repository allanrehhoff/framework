<?php

/**
 * This PHP class provides static methods for escaping HTML and string entities
 * to prevent cross-site scripting (XSS) attacks.
 *
 * It offers a convenient way to sanitize user-generated content before displaying it in HTML output.
 * The class uses the htmlspecialchars() function to escape special characters and preserve the integrity of the content.
 */
class Escape {
	/**
	 * Escapes special characters in a string for use in HTML.
	 *
	 * @param string $string The string to escape.
	 * @return string The escaped string.
	 */
	public static function string(string $string): string {
		return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
	}

	/**
	 * Escapes an array of strings for use in HTML.
	 *
	 * @param array $array The array to escape.
	 * @return array The escaped array.
	 */
	public static function array(array $array): array {
		$escapedArray = [];

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$escapedArray[$key] = self::array($value);
			} elseif (is_string($value)) {
				$escapedArray[$key] = self::string($value);
			} else {
				$escapedArray[$key] = $value;
			}
		}

		return $escapedArray;
	}

	/**
	 * Escapes an object's properties for use in HTML.
	 *
	 * @param object $iStdClass The object to escape.
	 * @return object The escaped object.
	 */
	public static function object(object $iStdClass): object {
		$escapedObject = new \stdClass();

		foreach ($iStdClass as $key => $value) {
			if (is_string($value)) {
				$escapedObject->$key = self::string($value);
			} elseif (is_object($value)) {
				$escapedObject->$key = self::object($value);
			} else {
				$escapedObject->$key = $value;
			}
		}

		return $escapedObject;
	}
}
