<?php
namespace Core {   
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

		public function __construct(string $string) {
			$base = ucwords(preg_replace("/\W+/", ' ', strtolower($string)));
			$parts = explode(' ', $base);
			$parts = array_map("ucfirst", $parts);
			$base = implode('', $parts);

			$this->sanitizedControllerName = $base;

			$this->sanitizedControllerClass = "\\".$base . 'Controller';
		}

		public function __toString() {
			return $this->sanitizedControllerName;
		}

		public function getSanitizedControllerName() : string {
			return $this->sanitizedControllerName;
		}

		public function getSanitizedControllerClass() : string {
			return $this->sanitizedControllerClass;
		}
	}
}