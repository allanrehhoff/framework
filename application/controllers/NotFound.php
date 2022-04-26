<?php
	/**
	 * Default controller for paths that cannot be routed.
	 * @author Allan Thue Rehhoff
	 */
	class NotFoundController extends Controller {

		/**
		 * Constructs an indicates the path wasn't found
		 * @return void
		 */
		public function index() {
			header("HTTP/1.0 404 Not Found");

			$this->setTitle("Not found");
			$this->setView("notfound");
		}
	}