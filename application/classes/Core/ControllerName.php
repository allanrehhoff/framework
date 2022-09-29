<?php
namespace Core {
	use \Exception;

	/**
	 * Sanitizes and validates a controller class name is valid for use.
	 */
	class ControllerName {
		/**
		 * @var string The string to be used as base
		 */
		private $string = '';

		/**
		 * @var string Holds the sanitized name.
		 */
		private $sanitizedControllerName = '';

		/**
		 * @var string Holds the sanitized class.
		 */
		private $sanitizedControllerClass = '';

		/**
		 * @param string Takes a single argument as a string,
		 * 				this will sanitized to a valid controller class
		 * @throws \Core\Exception\Logic
		 * @return void
		 */
		public function __construct(string $string) {
			$controllerClassParts = [];

			foreach(explode('/', $string) as $segment) {
				$base = preg_replace("/\W+/", ' ', $segment);
				$words = explode(' ', $base);
				$words = array_map("ucfirst", $words);

				$controllerClassParts[] = implode('', $words);
			}

			$controllerClass = implode("\\", $controllerClassParts);
			$controllerClass .= "Controller";

			if(class_exists($controllerClass) === false) {
				$controllerClass = "NotFoundController";
			}

			$iReflectionClass = new \ReflectionClass($controllerClass); 

			// Constrollers must not have a constructor
			// as Core\Application relies on a constroller instance
			// to set the controller parent.
			// e.g. Header and Footer children being set in Controller
			if($iReflectionClass->getConstructor() !== null) {
				throw new \Core\Exception\Logic("Found defined constructor in " . $controllerClass . ". Use ".$controllerClass."::start(); instead.");
			}

			// Defined controllers should always extend on the master controller
			if($iReflectionClass->isSubclassOf("Controller") !== true) {
				throw new \Core\Exception\Logic($controllerClass." must derive from \Controller 'extends \Controller'.");
			}

			$this->sanitizedControllerClass = $controllerClass;
		}

		/**
		 * Returns the sanitized controller class
		 * @return string
		 */
		public function getSanitizedControllerClass() : string {
			return $this->sanitizedControllerClass;
		}

		/**
		 * Also returns the sanitized controller class
		 * @return string
		 */
		public function __toString() {
			return $this->getSanitizedControllerClass();
		}
	}
}