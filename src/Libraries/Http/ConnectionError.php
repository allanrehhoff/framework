<?php
/**
 * Exception class used by the library to distinct any thrown exception from a standard PHP Exception
 * @extends Exception
 * @package Http\Request
 */
namespace Http {
	class ConnectionError extends \Exception {
		/**
		 * ConnectionError constructor.
		 *
		 * @param string $message Error message.
		 * @param int $code Error code (default is 0).
		 * @param \Exception|null $previous Previous exception (default is null).
		 */
		public function __construct(string $message, int $code = 0, \Exception $previous = null) {
			parent::__construct($message, $code, $previous);
		}
	}
}
