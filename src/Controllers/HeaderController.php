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

			$this->response->data["bodyClasses"] = $this->getBodyTagClasses();
		}

		/**
		 * Determines classes suiteable for the <body> tag
		 * These classes can be used for easier identification of controller and view files used
		 * or CSS styling for specific conditions
		 * 
		 * @return string An escaped string suitable for printing to the 'class' attribute of the <body> tag
		 */
		private function getBodyTagClasses() : string {
			$controllerName = $this->getApplication()->getExecutedClassName()->toStringWithoutSuffix();
			$methodName 	= $this->getApplication()->getCalledMethodName()->toStringWithoutSuffix();

			$bodyClasses = [];
			$bodyClasses[] = $controllerName;
			$bodyClasses[] = $controllerName . '-' . $methodName;
			$bodyClasses[] = $this->getResponse()->getView();

			foreach($this->getChildren() as $childControllerName) {
				$bodyClasses[] = $childControllerName->toStringWithoutSuffix();
			}

			foreach($this->getRequest()->getArguments() as $arg) {
				$bodyClasses[] = $arg;
			}

			foreach($bodyClasses as $i => $bodyClass) {
				$bodyClasses[$i] = strtolower($bodyClass);
			}

			$classesString = implode(' ', array_unique($bodyClasses));

			return \HtmlEscape::escape($classesString);
		}
	}