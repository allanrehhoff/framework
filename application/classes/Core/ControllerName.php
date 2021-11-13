<?php
namespace Core {
	use \Exception;
	use \ReflectionClass;

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
		* @return void
		*/
		public function __construct(string $string) {
			$base = ucwords(preg_replace("/\W+/", ' ', strtolower($string)));
			$parts = explode(' ', $base);
			$parts = array_map("ucfirst", $parts);
			$base = implode('', $parts);

			$controllerClass =  "\\".$base . 'Controller';

			if(is_subclass_of($controllerClass, "Controller") !== true) {
				throw new Exception($controllerClass." must derive from \Controller 'extends \Controller'.");
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