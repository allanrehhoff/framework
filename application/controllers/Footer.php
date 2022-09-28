<?php
	class FooterController extends Controller {
		public function index() {
			// Do not allow this controller to be access directly
			if($this->getParent() == null) throw new \Core\Exception\NotFound();

			$this->data["footer"] = $this->getView("footer");
			$this->data["stylesheets"] = $this->iAssets->getStylesheets("header");
		}
	}