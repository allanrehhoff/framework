<?php
	class FooterController extends Controller {
		public function index() {
			// Do not allow this controller to be access directly
			if($this->getParent() == null) throw new \Core\HttpError\NotFound;

			$this->response->data["footer"] = $this->getTheme()->getTemplatePath("footer");
			$this->response->data["stylesheets"] = $this->getTheme()->getAssets()->getStylesheets("header");
		}
	}