<?php
	/**
	 * Controller for paths that is inaccessible in the current environment.
	 */
	class ForbiddenController extends Controller {

		/**
		 * Constructs an indicates the path was not allowed.
		 * Paths are routed here when \Core\StatusCode\Forbidden is thrown
		 * @return void
		 */
		public function index(): void {
			$this->response->setTitle("Forbidden");
			$this->response->setView("forbidden");
		}
	}