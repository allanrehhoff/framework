<?php
namespace Core\Exception {
	/**
	 * Throw this whenever you want to redirect the current controller to a "forbidden" controller
	 */
	class Forbidden extends \Exception implements \Core\Contract\HttpExceptionInterface {
		/**
		 * @return int
		 */
		public function getHttpCode(): int { return 403; }
	}
}