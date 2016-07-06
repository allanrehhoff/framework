<?php
abstract class Singleton {
	protected function __construct(){ }

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