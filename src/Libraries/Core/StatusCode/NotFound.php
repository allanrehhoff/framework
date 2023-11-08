<?php
namespace Core\StatusCode {

	use \Core\StatusCode\StatusCode;

	/**
	 * Throw this whenever you want to redirect the current controller to a not found controller
	 */
	class NotFound extends StatusCode {
		/**
		 * @return int
		 */
		public static function getHttpCode(): int { return 404; }
	}
}