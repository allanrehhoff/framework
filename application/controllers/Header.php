<?php
	class HeaderController extends Controller {
		public function index() {
			// Do not allow this controller to be access directly
			if($this->getParent() == null) throw new \Core\NotFoundException();

			$this->data["header"] = $this->getView("header");
			$this->data["javascript"]  = $this->iAssets->getJavascript("footer");
		}
	}