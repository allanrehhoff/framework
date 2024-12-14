<?php

namespace Core\StatusCode;

use \Core\StatusCode\StatusCode;

/**
 * Throw this whenever you want to redirect the current controller to a "forbidden" controller
 */
class NotAcceptable extends StatusCode {

	/**
	 * Treat this HTTP code as a bodyless response
	 * This class is usually constructed outside
	 * of the scope where StatusCode is caught
	 * and handled by a controller.
	 */
	public function __construct() {
		\http_response_code(self::getHttpCode());
		exit;
	}

	/**
	 * @return int
	 */
	public static function getHttpCode(): int {
		return 406;
	}
}
