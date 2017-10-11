<?php
/**
* Extend this class to create a singletong class.
* This class is deprecated, use Registry::set(object) to create a globally accessible instance.
* @deprecated Deprecated since "v3.0-alpha2"
*/
abstract class Singleton {
	protected function __construct() { }

	final private function __clone() { }
	
	final public static function getInstance() {
		static $instances = [];

		$calledClass = get_called_class();

		if (!isset($instances[$calledClass])) {
			$instances[$calledClass] = new $calledClass();
		}

		return $instances[$calledClass];
	}

}