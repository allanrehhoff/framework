<?php
namespace Core {   
	class ControllerName {
		/**
		* @var string The string to be used as method name
		*/
		private $string = '';

		/**
		* @var string Holds the sanitized method name.
		*/
		private $sanitizedControllerName = '';

		public function __construct(string $string) {
			$base = ucwords(preg_replace("/\W+/", ' ', strtolower($string)));
			$parts = explode(' ', $base);
			$parts = array_map("ucfirst", $parts);
			$base = implode('', $parts);

			$this->sanitizedControllerName = "\\".$base . 'Controller';
		}

		public function __toString() {
			return $this->sanitizedControllerName;
		}

		public function getSanitiedControllerName() : string {
			return $this->sanitizedControllerName;
		}
	}
}