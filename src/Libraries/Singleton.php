<?php
	/**
	 * Class used to hold global state objects.
	 * Such as database connections, configurations and environments.
	 */
	final class Singleton {
		/**
		 * @var array Stores the data objects.
		 */
		private static $store = [];

		/**
		 * Allow direct calling of aliases 
		 * @param string $name The $name argument is the name of the method being called
		 * @param array $arguments The $arguments argument is an enumerated array containing the parameters passed to the $name'ed method. 
		 * @return mixed 
		 */
		public static function __callStatic(string $name, array $arguments) : mixed {
			return self::get($name);
		}

		/**
		 * Get an object by it's class name, namespaces included.
		 * Instatiates the object if not already existing.
		 * 
		 * @param string $key alias/classname to retrieve from global state
		 * @return mixed A class object
		 */
		public static function get(string $key) {
			return self::$store[$key] ?? null;
		}

		/**
		 * Stores a given object by it's class name, namespaces included
		 * 
		 * @param object $class The resource class to have a global state
		 * @param ?string $alias An alias to save the resource by, if null class name of object will be used
		 * @return object The instance just stored. 
		 */
		public static function set(object $object, ?string $alias = null) {
			$key = $alias ?? $object::class;

			self::$store[$key] = $object;

			return $object;
		}

		/**
		 * Checks if the registry contains a given object.
		 * 
		 * @param string $key Check if this key/alias is available globally
		 * @return bool
		 */
		public static function has(string $key) : bool {
			return isset(self::$store[$key]);
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