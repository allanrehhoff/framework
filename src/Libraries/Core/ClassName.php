<?php
namespace Core {
	/**
	 * Sanitizes and validates a controller class name is valid for use.
	 */
	final class ClassName extends MVCStructure {
		/**
		 * @param string $string Takes a single argument as a string,
		 * 				 this will sanitized to a valid controller class
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

			$this->sanitizedString = $controllerClass;
		}
	}
}