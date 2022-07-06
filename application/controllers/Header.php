<?php
	class HeaderController extends Controller {
		public function index() {
			// Do not allow this controller to be access directly
			if($this->getParent() == null) throw new \Core\NotFoundException();

			// If a header has been set, e.g. by a login page
			// do not overwrite it.
			if(!isset($this->data["header"])) {
				$this->data["header"] = $this->getView("header");
			}
		}
	}