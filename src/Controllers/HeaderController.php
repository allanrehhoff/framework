<?php
	class HeaderController extends Controller {
		public function index() {
			// Do not allow this controller to be access directly
			if($this->getParent() == null) throw new \Core\HttpError\NotFound;

			$this->response->data["header"] = $this->template->getPath("header");

			$this->response->data["stylesheets"] = array_merge(
				$this->response->data["stylesheets"] ?? [],
				$this->template->assets->getStylesheets("header")
			);

			$this->response->data["javascript"] = array_merge(
				$this->response->data["javascript"] ?? [],
				$this->template->assets->getJavascript("header")
			);
		}
	}