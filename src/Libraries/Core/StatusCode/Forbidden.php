<?php

namespace Core\StatusCode;

/**
 * Throw this whenever you want to redirect the current controller to a "forbidden" controller
 */
class Forbidden extends \Exception implements StatusCode {

	/**
	 * @return int
	 */
	public static function getHttpCode(): int {
		return 403;
	}

	/**
	 * Returns a class name matching the name
	 * of the http error exception being thrown.
	 * @return \Core\ClassName
	 */
	public static function getClassName(): \Core\ClassName {
		return new \Core\ClassName("StatusCode\Forbidden");
	}
}
