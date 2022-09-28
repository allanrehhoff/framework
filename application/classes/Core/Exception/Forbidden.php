<?php
namespace Core\Exception {
	class Forbidden extends \Exception {
		/**
		 * Throw this whenever you want to redirect the current controller to a "forbidden" controller
		 */
		public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null) {
			parent::__construct($message, $code, $previous);
		}
	}
}