<?php
namespace Database {
	/**
	* DatabaseException is thrown when the DbConnection class fails to connect or execute a query.
	* @extends Exception
	*/
	class DatabaseException extends \Exception {
		public function __construct($message, $code = 0, Exception $previous = null) {
			parent::__construct($message, $code, $previous);
		}
	}
}