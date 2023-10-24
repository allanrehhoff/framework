<?php
	/**
	 * Resource to hold global state objects, used to avoid the usage of singletons.
	 */
	final class Singleton {
		/**
		 * @var array Stores the data objects.
		 */
		private static $data = [];

		/**
		 * Get an object by it's class name, namespaces included.
		 * Instatiates the object if not already existing.
		 * 
		 * @param string $key alias/classname to retrieve from global state
		 * @return mixed A class object
		 */
		public static function get(string $key) {
			return self::$data[$key] ?? null;
		}

		/**
		 * Stores a given object by it's class name, namespaces included
		 * 
		 * @param mixed $class The resource class to have a global state
		 * @param ?string $alias An alias to save the resource by, if null class name of object will be used
		 * @return object The instance just stored. 
		 */
		public static function set(mixed $class, ?string $alias = null) {
			if($alias === null) {
				$key = get_class($class);
			} else {
				$key = $alias;
			}

			self::$data[$key] = $class;

			return $class;
		}

		/**
		 * Checks if the registry contains a given object.
		 * 
		 * @param string $key Check if this key/alias is available globally
		 * @return bool
		 */
		public static function has(string $key) : bool {
			return isset(self::$data[$key]);
		}

		/**
		 * Get core application configuration object
		 * 
		 * @return \Configuration
		 */
		public static function getConfiguration() : \Configuration {
			return self::get("Configuration");
		}

		/**
		 * @return \Environment
		 */
		public static function getEnvironment() : \Environment {
			return self::get("Environment");
		}

		/**
		 * Get instance of Database\Connection
		 * 
		 * @return \Database\Connection
		 */
		public static function getDatabaseConnection() : \Database\Connection {
			return self::get("Database\Connection");
		}
	}