<?php
class Singleton {
	private static $instance;

	private function __construct() {

	}

	public static function getInstance() {
		if (!isset(self::$instance)) {
			$obj = get_called_class(); 
        	self::$instance = new $obj;
		}

		return self::$instance;
	}
}