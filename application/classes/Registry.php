<?php
	/**
	* Registry to hold class objects, used to avoid the usage of singletons.
	* @author Allan Rehhoff
	*/
	final class Registry {
		/**
		* Stores the data objects.
		*/
		private static $data = array();

		/**
		* Get an object by it's class name, namespaces included.
		* @return (object) on success, null if the object is nowhere to be found
		*/
		public static function get($key) {
			return (isset(self::$data[$key]) ? self::$data[$key] : NULL);
		}

		/**
		* Stores a given object by it's class name, namespaces included
		* @return (object) The instance just stored. 
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
		public static function has($key) {
			return isset(self::$data[$key]);
		}
	}