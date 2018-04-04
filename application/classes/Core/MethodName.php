<?php
namespace Core {   
	class MethodName {
		/**
		* @var The string to be used as method name
		*/
		private $string = '';

		/**
		* @var Holds the sanitized method name.
		*/
		private $sanitizedMethodName = '';

		/**
		* @const Default method name to be used, if a given method doesn't exist on a class.
		*/
		const DEFAULT = "index";

		/**
		* @param (string) Takes a single argument as a string, this will be the method name to use.
		* @return void
		*/
		public function __construct(string $string) {
			$this->method = $string;
			$this->sanitizeToValidMethodName($this->method);
		}

		/**
		* Sanitizes the given string to a PHP5 compatible camelCase method/function name.
		* @param (string) The string to sanitize.
		* @return string the sanitized method name
		*/
		private function sanitizeToValidMethodName(string $string) {
			preg_match_all('/\w+/', $string, $temp);

			$pregLastError = preg_last_error();

			if($pregLastError !== PREG_NO_ERROR) {
				$pregErrorMap = [
					PREG_INTERNAL_ERROR => "Internal PCRE error.",
					PREG_BACKTRACK_LIMIT_ERROR => "PCRE's backtracking limit was reached, try ini_set('pcre.backtrack_limit', int).",
					PREG_RECURSION_LIMIT_ERROR => "PCRE's recursion limit was reached, pcre.recursion_limit may not be set to high as it consume all the available process stack.",
					PREG_BAD_UTF8_ERROR => "Malformed UTF8 data in string.",
					PREG_BAD_UTF8_OFFSET_ERROR => "The offset didn't correspond to the begin of a valid UTF-8 code point",
				];

				// Only in PHP7
				if(defined("PREG_JIT_STACKLIMIT_ERROR") == true) $pregErrorMap[PREG_JIT_STACKLIMIT_ERROR] = "PCRE function call failed due to limited JIT stack space.";

				throw new MethodNameException($pregErrorMap[$pregLastError], $pregLastError);
			}

			foreach ($temp[0] as $key => $word) {
				$temp[$key] = ucfirst(strtolower($word)); 
			}

			return $this->sanitizedMethodName = lcfirst(implode('', $temp));
		}

		/**
		* Returns the sanitized method name
		* @return string
		*/
		public function getSanitizedMethodName() {
			return $this->sanitizedMethodName;
		}

		/**
		* Also returns the sanitized method name
		* @return string
		*/
		public function __toString() {
			return $this->getSanitizedMethodName();
		}
	}
}