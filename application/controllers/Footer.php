<?php
	class FooterController extends Controller {
		public function index() {
			// If a footer has been set, e.g. by login page
			// do not overwrite it.
			if(!isset($this->data["footer"])) {
				$this->data["footer"] = $this->getView("footer");
			}
		}
	}