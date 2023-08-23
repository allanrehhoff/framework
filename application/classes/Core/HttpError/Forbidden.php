<?php
namespace Core\HttpError {
	/**
	 * Throw this whenever you want to redirect the current controller to a "forbidden" controller
	 */
	class Forbidden extends StatusCode {
		/**
		 * @return int
		 */
		public function getHttpCode(): int { return 403; }
	}
}