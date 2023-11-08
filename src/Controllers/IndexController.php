<?php
	/**
	 * This is the frontpage (index) specific controller, use it as your boilerplate for other controllers.
	 * @author Allan Rehhoff
	 */
	class IndexController extends Controller {
		public function index() {
			$this->response->data["intro"] = "Hello world";

			$this->response->setTitle("Frontpage");
			$this->response->setView("index");
		}
	}