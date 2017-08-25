<?php
	/**
	* Registry to hold class objects, used to avoid the usage of singletons.
	*/
	final class Registry {
		private static $data = array();

		public static function get($key) {
			return (isset(self::$data[$key]) ? self::$data[$key] : NULL);
		}

		public static function set($class) {
			$key = get_class($class);
			self::$data[$key] = $class;

			return $class;
		}

		public static function has($key) {
			return isset(self::$data[$key]);
		}
	}