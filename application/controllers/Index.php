<?php
	/**
	* This is the frontpage (index) specific controller, use it as your boilerplate for other controllers.
	* @author Allan Rehhoff
	*/
	class IndexController extends Controller {
		public function index() {
			$this->setTitle("Frontpage");
			$this->data["intro"] = "A sample variable.";
		}
	}