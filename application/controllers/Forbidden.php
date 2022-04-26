<?php
	/**
	 * Default controller for paths that cannot be routed.
	 * @author Allan Thue Rehhoff
	 */
	class ForbiddenController extends Controller {

		/**
		 * Constructs an indicates the path wasn't found
		 * @return void
		 */
		public function index() {
			header("HTTP/1.0 501 Forbidden");

			$this->setTitle("Forbidden");
			$this->setView("forbidden");
		}
	}