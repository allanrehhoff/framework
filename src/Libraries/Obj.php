<?php

/**
 * Class Obj
 *
 * Utility class for object-related operations with null safety.
 */
class Obj {
	/**
	 * Get a property value from an object, with null safety.
	 *
	 * @param null|object $object The object to retrieve the property from.
	 * @param string $property The property name.
	 * @param mixed $default The default value to return if the property doesn't exist.
	 *
	 * @return mixed The property value or default if not set.
	 */
	public static function get(null|object $object, string $property, mixed $default = null): mixed {
		if ($object === null || !property_exists($object, $property)) return $default;
		return $object->$property;
	}

	/**
	 * Set a property value on an object, with null safety.
	 *
	 * @param null|object $object The object to set the property on.
	 * @param string $property The property name.
	 * @param mixed $value The value to set.
	 *
	 * @return void
	 */
	public static function set(null|object $object, string $property, mixed $value): void {
		if ($object === null) return;
		$object->$property = $value;
	}

	/**
	 * Escapes an object's properties for use in HTML.
	 *
	 * @param object $iStdClass The object to escape.
	 * @return object The escaped object.
	 */
	public static function safe(null|object $iStdClass): object {
		$escapedObject = new \stdClass();

		if ($iStdClass === null) return $escapedObject;

		foreach ($iStdClass as $key => $value) {
			if (is_string($value)) {
				$escapedObject->$key = Str::safe($value);
			} elseif (is_object($value)) {
				$escapedObject->$key = self::safe($value);
			} else {
				$escapedObject->$key = $value;
			}
		}

		return $escapedObject;
	}

	/**
	 * Check if a property exists on an object, with null safety.
	 *
	 * @param null|object $object The object to check.
	 * @param string $property The property name.
	 *
	 * @return bool True if the property exists, false otherwise.
	 */
	public static function has(null|object $object, string $property): bool {
		if ($object === null) return false;
		return property_exists($object, $property);
	}

	/**
	 * Convert an object to an array.
	 *
	 * @param null|object $object The object to convert.
	 * @return array An array representation of the object, or an empty array if the object is null.
	 */
	public static function toArray(null|object $object): array {
		if ($object === null) return [];
		return (array) $object;
	}

	/**
	 * Convert an object to a JSON string.
	 *
	 * @param null|object $object The object to convert.
	 * @return null|string The JSON string representation of the object, or null if the object is null.
	 */
	public static function toJson(null|object $object): null|string {
		if ($object === null) return null;
		return json_encode($object);
	}
}
