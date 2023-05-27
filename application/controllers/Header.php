<?php
	class HeaderController extends Controller {
		public function index() {
			// Do not allow this controller to be access directly
			if($this->getParent() == null) throw new \Core\Exception\NotFound();

			$this->response->data["header"] = $this->theme->getTemplatePath("header");
			$this->response->data["javascript"]  = $this->theme->getAssets()->getJavascript("footer");
		}
	}