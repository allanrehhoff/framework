<?php
	/**
	 * This is the frontpage (index) specific controller, use it as your boilerplate for other controllers.
	 */
	class IndexController extends Controller {
		/**
		 * Default configured entry point for application
		 * @return void
		 */
		public function index(): void {
			$this->response->data["intro"] = "Hello world";

			$this->response->setTitle("Frontpage");
			$this->response->setView("index");
		}
	}