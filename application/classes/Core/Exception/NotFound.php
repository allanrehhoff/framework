<?php
namespace Core\Exception {
	/**
	 * Throw this whenever you want to redirect the current controller to a not found controller
	 */
	class NotFound extends \Exception {
		/**
		 * @return int
		 */
		public function getHttpCode(): int { return 404; }
	}
}