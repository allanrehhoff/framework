<?php
	class NotfoundController extends \Core\Controller {
		public function __construct() {
			parent::__construct();
			header("HTTP/1.0 404 Not Found");
		}

		public function index() {
			$this->setTitle("Not found");
			$this->setView("notfound");
			// One could log this 404 entry here.
		}
	}