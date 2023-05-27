<?php
namespace Core {
	/**
	 * Sanitizes and validates a controller class name is valid for use.
	 */
	class ClassName extends MVCStructure {
		/**
		 * @param string Takes a single argument as a string,
		 * 				this will sanitized to a valid controller class
		 * @throws \Core\Exception\Governance
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
			$controllerClass .= \Controller::class;

			// Constrollers must not have a constructor
			// as \Core\Application relies on a constroller instance
			// to set the controller parent, and dependency objects
			// Fx. Header and Footer children being set as children
			// in controllers, and \Core\Request being injected
			$iReflectionClass = new \ReflectionClass($controllerClass);
			$iReflectionMethod = $iReflectionClass->getConstructor();

			if($iReflectionMethod !== null && $iReflectionMethod->getDeclaringClass()->getName() !== \Controller::class) {
				throw new \Core\Exception\Governance("Illegal constructor method in " . $controllerClass . "; Use ".$controllerClass."::start(); instead.");
			}
			
			// Defined controllers should always extend on the master controller
			if($iReflectionClass->isSubclassOf("Controller") !== true) {
				throw new \Core\Exception\Governance($controllerClass." must derive from \Controller 'extends \Controller'.");
			}

			$this->sanitizedString = $controllerClass;
		}
	}
}