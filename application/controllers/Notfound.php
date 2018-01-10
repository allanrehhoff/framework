<?php
	/**
	* Default controller for paths that cannot be routed.
	* @author Allan Thue Rehhoff
	*/
	class NotfoundController extends \Core\Controller {

		/**
		* Constructs an indicates the path wasn't found
		* @return void
		*/
		public function __construct() {
			parent::__construct();
			header("HTTP/1.0 404 Not Found");

			// Force the "notfound.tpl.php" file, as arg(0) is potentially unknown at this point.
			// One should also log this 404 entry here.
			$this->setTitle("Not found");
			$this->setView("notfound");
		}
	}