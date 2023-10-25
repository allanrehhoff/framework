<?php
namespace Core {
	/**
	 * Sanitizes and validates a controller class name is valid for use.
	 */
	final class ClassName extends MVCStructure {
		/**
		 * @param string $string Takes a single request argument as a string,
		 * 				 this will be sanitized to a valid controller class
		 * @return void
		 */
		public function __construct(string $string) {
			$controllerClassParts = [];

			foreach(explode('/', $string) as $segment) {
				$words = preg_split("/\W+/", $segment, -1, PREG_SPLIT_NO_EMPTY);

				$parts = '';
				foreach($words as $value) $parts .= ucfirst($value);

				$controllerClassParts[] = $parts;
			}

			$controllerClass = implode("\\", $controllerClassParts);
			$controllerClass .= \Controller::class;

			$this->sanitizedString = $controllerClass;
		}
	}
}