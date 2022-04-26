<?php
	/**
	 * Resource to hold class objects, used to avoid the usage of singletons.
	 * @author Allan Rehhoff
	 */
	final class Resource {
		/**
		 * @var array Stores the data objects.
		 */
		private static $data = [];

		/**
		 * Get an object by it's class name, namespaces included.
		 * Instatiates the object if not already existing.
		 * @return object on success
		 */
		public static function get(string $key) {
			if(!self::has($key)) {
				self::set(new $key);
			}

			return (isset(self::$data[$key]) ? self::$data[$key] : NULL);
		}

		/**
		 * Stores a given object by it's class name, namespaces included
		 * @return object The instance just stored. 
		 */
		public static function set($class) {
			$key = get_class($class);
			self::$data[$key] = $class;

			return $class;
		}

		/**
		 * Checks if the registry contains a given object.
		 * @return bool
		 */
		public static function has(string $key) : bool {
			return isset(self::$data[$key]);
		}
	}