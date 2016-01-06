<?php
	/**
	* The registry holds all the essential components used by the application.
	* Such as configuration, database and the application itself
	*/
	final class Registry {
		private static $data = [];

		/**
		* Retrieve an object saved to the registry.
		* @param (string) $key variable name of the object to retrieve
		* @return object
		*/
		public static function get($key) {
			return (isset(self::$data[$key]) ? self::$data[$key] : NULL);
		}

		/**
		* Store an object in the registry.
		* @throws Exception
		* @param (string) $key variable name to save the object by.
		* @param (object) $value Object to be stored in the registry.
		* @return void
		*/
		public static function set($key, $value) {
			if(!is_object($value)) {
				throw new Exception("Only objects should be saved to the registry.");
			}
			self::$data[$key] = $value;
		}

		/**
		* Check if the registry contains a given object.
		* @param (string) $key Variable name of the object to look for.
		* @return boolean
		*/
		public function has($key) {
	    	return isset(self::$data[$key]);
	  	}
	}