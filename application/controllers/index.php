<?php
	class indexController extends \Core\Controller {
		public function __construct() {
			parent::__construct();
		}

		public function index() {
			$this->data["intro"] = "html goes here.";
		}
	}