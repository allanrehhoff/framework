<?php
/**
 * Exception used when remote answers with a non-successful HTTP code < 400
 * @extends Exception
 * @package Http\Request
 */
namespace Http {
	class HttpError extends \Exception {
		/**
		 * HttpError constructor.
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
