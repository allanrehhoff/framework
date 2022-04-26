<?php
namespace Core {
	use Exception;

	/**
	 * MethodNameException is raised upon failure to parse the configuration file.
	 * You should rarely have the need for catching this.
	 * @author Allan Thue Rehhoff
	 */
	class MethodNameException extends Exception {
		public function __construct($message, $code = 0, Exception $previous = null) {
			parent::__construct($message, $code, $previous);
		}
	}
}