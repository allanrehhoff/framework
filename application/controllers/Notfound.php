<?php
	/**
	* Default controller for paths that cannot be routed.
	* @author Allan Thue Rehhoff
	*/
	class NotfoundController extends \Core\Controller {
		/**
		* Constructsd an indicates the path wasn't found
		* @return void
		*/
		public function __construct() {
			parent::__construct();
			header("HTTP/1.0 404 Not Found");
		}

		/**
		* Default method called
		* @return void
		*/
		public function index() {
			$this->setTitle("Not found");

			// Force the "notfound.tpl.php" file, as arg(0) is potentially unknown at this point.
			$this->setView("notfound");

			// One could log this 404 entry here.
		}
	}