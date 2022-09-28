<?php
namespace Core\Exception {
	/**
	* MissingFile is throw when core is missing a required file.
	* Could be a config .json file.
	* You should rarely have the need for catching this.
	* @author Allan Thue Rehhoff
	*/
	class MissingFile extends \Exception {
		public function __construct($message, $code = 0, Exception $previous = null) {
			parent::__construct($message, $code, $previous);
		}
	}
}