<?php
	class IndexController extends \Core\Controller {
		public function __construct() {
			parent::__construct();
		}

		public function index() {
			$this->data["intro"] = "A sample variable.";
		}
	}