<?php
namespace Core\Exception {
	/**
	* ConfigurationParserException is raised upon failure to parse a configuration file.
	* You should rarely have the need for catching this.
	* @author Allan Thue Rehhoff
	*/
	class Configuration extends \Exception {
		public function __construct($message, $code = 0, Exception $previous = null) {
			parent::__construct($message, $code, $previous);
		}
	}
}