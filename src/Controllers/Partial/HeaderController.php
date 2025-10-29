<?php

namespace Partial;

class HeaderController extends \Controller {
	/**
	 * Default entry point for the header partial
	 * @return void
	 */
	public function index(): void {
		// Do not allow this controller to be access directly
		if ($this->getParent() == null) {
			throw new \Core\StatusCode\NotFound;
		}

		$this->response->data["header"] = $this->template->getViewPath("header");

		$this->response->data["stylesheets"] = \Arr::merge(
			$this->response->data["stylesheets"] ?? [],
			$this->template->assets->getStylesheets("header")
		);

		$this->response->data["javascript"] = \Arr::merge(
			$this->response->data["javascript"] ?? [],
			$this->template->assets->getJavascript("header")
		);

		$this->response->data["route"] = \Arr::map(
			\Registry::get("route"),
			fn($iMVCStructure) => \Str::safe(\Str::lower($iMVCStructure->toStringWithoutSuffix()))
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
	private function getBodyTagClasses(): string {
		$controllerName = \Registry::get("route")[0]->toStringWithoutSuffix();
		$methodName 	= \Registry::get("route")[1]->toStringWithoutSuffix();

		$bodyClasses = [];
		$bodyClasses[] = $controllerName;
		$bodyClasses[] = $controllerName . '-' . $methodName;
		$bodyClasses[] = $this->getResponse()->getView();

		foreach ($this->getChildren() as $childControllerName) {
			$bodyClasses[] = $childControllerName->toStringWithoutSuffix();
		}

		foreach ($this->getRequest()->getArguments() as $arg) {
			$bodyClasses[] = $arg;
		}

		foreach ($bodyClasses as $i => $bodyClass) {
			$bodyClasses[$i] = strtolower($bodyClass);
		}

		$classesString = implode(' ', array_unique($bodyClasses));

		return \Str::safe($classesString);
	}
}
