<?php
	/**
	 * Controller for paths that is inaccessible in the current environment.
	 */
	class ForbiddenController extends Controller {

		/**
		 * Constructs an indicates the path was not allowed
		 */
		public function index() {
			$this->response->setTitle("Forbidden");
			$this->response->setView("forbidden");
		}
	}