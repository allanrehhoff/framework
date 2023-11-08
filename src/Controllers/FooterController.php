<?php
	class FooterController extends Controller {
		public function index() {
			// Do not allow this controller to be access directly
			if($this->getParent() == null) throw new \Core\StatusCode\NotFound;

			$this->response->data["footer"] = $this->template->getPath("footer");

			$this->response->data["stylesheets"] = array_merge(
				$this->response->data["stylesheets"] ?? [],
				$this->template->assets->getStylesheets("footer")
			);

			$this->response->data["javascript"] = array_merge(
				$this->response->data["javascript"] ?? [],
				$this->template->assets->getJavascript("footer")
			);
		}
	}