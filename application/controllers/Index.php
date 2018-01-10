<?php
	/**
	* This is the frontpage (index) specific controller, use it as your boilerplate for other controllers.
	* @author Allan Rehhoff
	*/
	class IndexController extends \Core\Controller {
		public function __construct() {
			parent::__construct();

			$this->setTitle("Frontpage");
			$this->document->addJavascript("test.js");
			$this->data["intro"] = "A sample variable.";
		}
	}